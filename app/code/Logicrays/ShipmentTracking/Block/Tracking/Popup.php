<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Logicrays\ShipmentTracking\Block\Tracking;

use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Tracking popup
 *
 * @api
 * @since 100.0.2
 */
class Popup extends \Magento\Shipping\Block\Tracking\Popup
{
    const XML_PATH_SHIPMENTTRACKING_ENABLE = "shipmenttracking/shipmenttracking/enable";
    const XML_PATH_SHIPMENTTRACKING_CURL = "shipmenttracking/shipmenttracking/curl";
    const XML_PATH_SHIPMENTTRACKING_SITEID = "shipmenttracking/shipmenttracking/siteid";
    const XML_PATH_SHIPMENTTRACKING_PASSWORD = "shipmenttracking/shipmenttracking/password";
    const XML_PATH_BLUEDARTSHIPMENTTRACKING_ENABLE = "shipmenttracking/bluedartshipmenttracking/enable";
    const XML_PATH_BLUEDARTSHIPMENTTRACKING_LOGINID = "shipmenttracking/bluedartshipmenttracking/loginid";
    const XML_PATH_BLUEDARTSHIPMENTTRACKING_LICKEY = "shipmenttracking/bluedartshipmenttracking/lickey";
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $registry, $dateTimeFormatter, $data);
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    public function getDHLTrackingInfo($trackingNumber)
    {
        $curl = curl_init();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        if($this->_scopeConfig->getValue(self::XML_PATH_SHIPMENTTRACKING_ENABLE, $storeScope)){

            $this->_scopeConfig->isSetFlag(
                'contact/contact/enabled',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $siteCurl = $this->_scopeConfig->getValue(self::XML_PATH_SHIPMENTTRACKING_CURL, $storeScope);
            $siteID = $this->_scopeConfig->getValue(self::XML_PATH_SHIPMENTTRACKING_SITEID, $storeScope);
            $password = $this->_scopeConfig->getValue(self::XML_PATH_SHIPMENTTRACKING_PASSWORD, $storeScope);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $siteCurl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'<?xml version="1.0" encoding="UTF-8"?>
            <req:KnownTrackingRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com TrackingRequestKnown.xsd" schemaVersion="1.0">
                <Request>
                    <ServiceHeader>
                        <MessageReference>TrackingRequest_Single_AWB__</MessageReference>
                        <SiteID>'.$siteID.'</SiteID>
                        <Password>'.$password.'</Password>
                    </ServiceHeader>
                </Request>
                <LanguageCode>en</LanguageCode>
                <AWBNumber>'.$trackingNumber.'</AWBNumber>
                <LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>
                <PiecesEnabled>P</PiecesEnabled>
            </req:KnownTrackingRequest>',
                    CURLOPT_HTTPHEADER => array(
                            'Content-Type: text/xml',
                            'Cookie: _abck=EC223C05786D53EADDF94900F52020D8~-1~YAAQlSEPFxwWkzSOAQAAeVOUNwu/I+iaPPcpjeklW/ArApCzF1KLZNAlFwc2BoYP5WRfZgLx8kuYxybj03N/8YGD5/YxKJ+OfwMpauFk1Uf1KlW86u/1fFnWHa9+Rs72QqvUxAh6tjFJQNbdONtoCYG32BvPJCOcP5lW09rO1VZhhPHckfpb12JojxO5pJFZhKrVLQTKzHYKLp2EqcO3R59ZLzIAol6MZ4Gm/4z2AaI41yP6L7uH0R7Q52Tisr8mfhzk2PREr0UtZkNFJwrqKOFMmItA51x1daDNP3zkAgoE7iXUTMVHWJwP3WxE+4VbOrXj1VcptYJ6hBPGFsRGL6phu3vW9nnhbkbG~-1~-1~-1'
                    ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            // Parse the XML response
            $responseXml = new \SimpleXMLElement($response);

            // dd($responseXml->Response->Status->ActionStatus); // Failure
            if(isset($responseXml->Response->Status->ActionStatus) && $responseXml->Response->Status->ActionStatus == 'Failure'){
                return [];
            }

            $trackingData = [];
            
            foreach ($responseXml->AWBInfo->Status->ActionStatus as $value) {
                $trackingData['status'] = (string)$value;
                $trackingData["DestinationServiceArea"] = "";
                if((string)$value != 'No Shipments Found'){

                    foreach ($responseXml->AWBInfo->Pieces->PieceInfo->PieceEvent as $shipmentEvent) {
                        $data = [];
                        $data['date'] = (string)$shipmentEvent[0]->Date;
                        $data['formatedDate'] = date('d. F Y', strtotime($data['date']));
                        $data['dayName'] = (string)date('l', strtotime($data['date']));
                        $data['time'] = (string)$shipmentEvent->Time;
                        $data['localTime'] = date('H:i', strtotime($data['time']));
                        $data['description'] = (string)$shipmentEvent->ServiceEvent->Description;
                        $data['serviceDescription'] = (string)$shipmentEvent->ServiceArea->Description;
                        $trackingData["pieceId"] = (string)$responseXml->AWBInfo->Pieces->PieceInfo->PieceDetails->LicensePlate;
                        $trackingData["waybillNumber"] = (string)$responseXml->AWBInfo->Pieces->PieceInfo->PieceDetails->AWBNumber;
                        $trackingData["moreShippmentDetail"] = "Electronic Proof of Delivery";
                        $trackingData["EventCode"] = (string)$shipmentEvent->ServiceEvent->EventCode;

                        $trackingData[] = $data;
                        if((string)$shipmentEvent->ServiceEvent->EventCode == 'OK'){
                            $trackingData["formatedDate"] = $data["formatedDate"];
                            $trackingData["localTime"] = $data["localTime"]."";
                            $trackingData["DestinationServiceArea"] = (string)$shipmentEvent->ServiceArea->Description;
                        }
                        if((string)$shipmentEvent->ServiceEvent->EventCode == 'PU'){
                            $trackingData["OriginServiceArea"] = (string)$shipmentEvent->ServiceArea->Description;
                        }
                    }
                }
            }
        }
        array_multisort($trackingData,SORT_DESC);
        if(count($trackingData) && isset($trackingData['EventCode'])){
            $trackingData["formatedDate"] = $trackingData[0]['formatedDate'];
            $trackingData["localTime"] = $trackingData[0]['localTime'];
        }
        else{
            $trackingData = [];
        }
        return $trackingData;
    }
    
    public function getBlueDartTrackingInfo($trackingNumber){

        $trackingData = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if($this->_scopeConfig->getValue(self::XML_PATH_BLUEDARTSHIPMENTTRACKING_ENABLE, $storeScope)){

            $curl = curl_init();

            $loginID = $this->_scopeConfig->getValue(self::XML_PATH_BLUEDARTSHIPMENTTRACKING_LOGINID, $storeScope);
            $licKey = $this->_scopeConfig->getValue(self::XML_PATH_BLUEDARTSHIPMENTTRACKING_LICKEY, $storeScope);

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.bluedart.com/servlet/RoutingServlet?handler=tnt&action=custawbquery&scan=1&loginid='.$loginID.'&lickey='.$licKey.'&verno=1.3&awb=awb&format=json&numbers='.$trackingNumber,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_HTTPHEADER => array(
                'Cookie: BIGipServerpl_api-bluedart.dhl.com_443=!nwyIxCZiZ+FLs5ffR3BsqrvQUUbjCB14iT/ODvYtazpxJPxEtCMC2Kn8aOJx3zQJjOAfRsx6/W8Yy/Q=; JSESSIONID=UQFsd_X4h8wg2Izp1xVDr1TK2hC7c5iHYfiqwztf.mykullspc000960'
              ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response);
            curl_close($curl);

            if(!isset($response->ShipmentData->Error)){
                $trackingData["localTime"] = date('H:i', strtotime($response->ShipmentData->Shipment[0]->PickUpTime));
                $trackingData["waybillNumber"] = $trackingNumber;
                $trackingData["formatedDate"] = (string)$response->ShipmentData->Shipment[0]->PickUpDate;
                $trackingData["OriginServiceArea"] = (string)$response->ShipmentData->Shipment[0]->Origin;
                $trackingData["DestinationServiceArea"] = (string)$response->ShipmentData->Shipment[0]->Destination;
                $trackingData["status"] = (string)$response->ShipmentData->Shipment[0]->Status;
                $trackingData["ExpectedDeliveryDate"] = (string)$response->ShipmentData->Shipment[0]->ExpectedDelivery;
                $trackingData["Scans"] = $response->ShipmentData->Shipment[0]->Scans;

                if((string)$response->ShipmentData->Shipment[0]->Status == "SHIPMENT DELIVERED"){
                    $trackingData["EventCode"] = "OK";
                    $trackingData["ExpectedDeliveryDate"] = (string)$response->ShipmentData->Shipment[0]->StatusDate;
                    $trackingData["StatusTime"] = (string)$response->ShipmentData->Shipment[0]->StatusTime;
                    $trackingData["ReceivedBy"] = (string)$response->ShipmentData->Shipment[0]->ReceivedBy;
                }
                else{
                    $trackingData["EventCode"] = (string)$response->ShipmentData->Shipment[0]->Status;
                }
                $trackingData["RefNo"] = (string)$response->ShipmentData->Shipment[0]->RefNo;
            }
        }
        return $trackingData;
    }
}
