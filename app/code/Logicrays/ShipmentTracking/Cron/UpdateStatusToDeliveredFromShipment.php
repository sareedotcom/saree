<?php

namespace Logicrays\ShipmentTracking\Cron;

use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class UpdateStatusToDeliveredFromShipment
{
    const XML_PATH_SHIPMENTTRACKING_ENABLE = "shipmenttracking/shipmenttracking/enable";
    const XML_PATH_SHIPMENTTRACKING_CURL = "shipmenttracking/shipmenttracking/curl";
    const XML_PATH_SHIPMENTTRACKING_SITEID = "shipmenttracking/shipmenttracking/siteid";
    const XML_PATH_SHIPMENTTRACKING_PASSWORD = "shipmenttracking/shipmenttracking/password";

    protected $quotesFactory;
    protected $storeManager;

    public function __construct(
        ShipmentCollectionFactory $shipmentCollectionFactory,
        Order $order,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->order = $order;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;

    }

    /**
     * Cron execute
     *
     * @return void
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/OrderStatusUpdateByShipment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            // Get the shipment collection
            if($this->_scopeConfig->getValue(self::XML_PATH_SHIPMENTTRACKING_ENABLE, $storeScope)){

                $shipmentCollection = $this->shipmentCollectionFactory->create();
                $shipmentCollection->getSelect()->join(
                    ['track' => $shipmentCollection->getTable('sales_shipment_track')],
                    'main_table.entity_id = track.parent_id',
                    []
                );
                $shipmentCollection->getSelect()->join(
                    ['order' => $shipmentCollection->getTable('sales_order')],
                    'main_table.order_id = order.entity_id',
                    []
                );
                $shipmentCollection->addFieldToFilter('track.carrier_code', 'dhl');
                $shipmentCollection->addFieldToFilter('order.status', ['in' => ['complete']]);
                foreach ($shipmentCollection as $shipment) {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    $tracks = $shipment->getTracks();
                    foreach ($tracks as $track) {
                        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                        if ($track->getCarrierCode() === 'dhl') {

                            $curl = curl_init();
                            $storeScope = ScopeInterface::SCOPE_STORE;

                            $this->_scopeConfig->isSetFlag(
                                'contact/contact/enabled',
                                ScopeInterface::SCOPE_STORE
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
                                <AWBNumber>'.$track->getTrackNumber().'</AWBNumber>
                                <LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>
                                <PiecesEnabled>P</PiecesEnabled>
                            </req:KnownTrackingRequest>',
                                    CURLOPT_HTTPHEADER => array(
                                            'Content-Type: text/xml',
                                            'Cookie: _abck=EC223C05786D53EADDF94900F52020D8~-1~YAAQlSEPFxwWkzSOAQAAeVOUNwu/I+iaPPcpjeklW/ArApCzF1KLZNAlFwc2BoYP5WRfZgLx8kuYxybj03N/8YGD5/YxKJ+OfwMpauFk1Uf1KlW86u/1fFnWHa9+Rs72QqvUxAh6tjFJQNbdONtoCYG32BvPJCOcP5lW09rO1VZhhPHckfpb12JojxO5pJFZhKrVLQTKzHYKLp2EqcO3R59ZLzIAol6MZ4Gm/4z2AaI41yP6L7uH0R7Q52Tisr8mfhzk2PREr0UtZkNFJwrqKOFMmItA51x1daDNP3zkAgoE7iXUTMVHWJwP3WxE+4VbOrXj1VcptYJ6hBPGFsRGL6phu3vW9nnhbkbG~-1~-1~-1'
                                    ),
                            ));

                            $response = curl_exec($curl);

                            $responseXml = new \SimpleXMLElement($response);
                            foreach ($responseXml->AWBInfo->Pieces->PieceInfo->PieceEvent as $shipmentEvent) {
                                if((string)$shipmentEvent->ServiceEvent->EventCode == 'OK'){
                                    $message = "Order ID: ".$shipment->getOrderId().", Shipment ID: ".$shipment->getIncrementId()." DHL Tracking Number: ".$track->getTrackNumber(); 
                                    $order = $this->order->load($shipment->getOrderId()); 
                                    $order->setState($order->getState())->setStatus("delivered");
                                    $orderMessage = "Order status change has change by cron, Shipment ID: ".$shipment->getIncrementId()." DHL Tracking Number: ".$track->getTrackNumber(); 
                                    $order->addStatusHistoryComment($orderMessage);
                                    $order->save();
                                    $logger->info(print_r($message,true));
                                }
                            }
                            curl_close($curl);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $logger->error('Error processing DHL shipments: ' . $e->getMessage());
            $logger->info(print_r('Error: ' . $e->getMessage()));
            exit(1);
        }

        
    }
}
