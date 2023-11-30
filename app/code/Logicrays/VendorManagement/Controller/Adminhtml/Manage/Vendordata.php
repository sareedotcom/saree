<?php

namespace Logicrays\VendorManagement\Controller\Adminhtml\Manage;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
USE Logicrays\VendorManagement\Helper\Email;

class Vendordata extends \Magento\Framework\App\Action\Action
{    
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Email
     */
    private $email;

    /**
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param Email $email
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        Email $email
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->resourceConnection = $resourceConnection;
        $this->email = $email;
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        $whoteData = $this->context->getRequest()->getParams();
        if(isset($whoteData['itemId'])){
            $itemId = str_replace("vendorpopup","",$whoteData['itemId']);
            $connection  = $this->resourceConnection->getConnection();
            $select = $connection->select()
                                ->from(
                                    ['logicrays_vendor' => "logicrays_vendor"],
                                    ['email','phone'])
                                ->join(
                                    ['logicrays_vendor_products'=>'logicrays_vendor_products'],
                                    "logicrays_vendor_products.vendor_id = logicrays_vendor.vendor_id",
                                    ['product_id'])
                                ->where(
                                    "logicrays_vendor_products.product_id = ?",trim($itemId));
            $data = $connection->fetchRow($select);
            $data['itemId'] = trim($itemId);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(["data" => $data, "suceess" => true]);
            return $resultJson;
        }
        if(isset($whoteData['vendorEmail'])){
            $size = "<table><tbody>";
            $sizeForWhatsapp = "";
            if(isset($whoteData['optoin'])){
                foreach ($whoteData['optoin'] as $value) {
                    $options = explode("__",$value);
                    $size = $size."<tr><td>$options[0]: </td><td>$options[1]</td></tr>";
                    $sizeForWhatsapp = $sizeForWhatsapp.", ".$options[0].": ".$options[1];
                }
            }
            $size = $size."</tbody></table>";

            $data['ponumber'] = $whoteData['ponumber'];
            $data['vendor_name'] = $whoteData['vendor_name'];
            $data['vendorEmail'] = $whoteData['vendorEmail'];
            $data['vendorPhone'] = $whoteData['vendorPhone'];
            $data['vendor_code'] = $whoteData['vendor_code'];
            $data['qty'] = $whoteData['qty'];
            $data['comment_box'] = $whoteData['comment_box'];
            $data['orderIncrementId'] = $whoteData['orderIncrementId'];
            $data['size'] = $size;

            $this->email->sendEmail($data);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://saree.bobot.in/workflow/webhook/88d80156-f47c-41fa-b09b-83e3bc5755dd',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'
                {
                    "platform": "saree",
                    "phone": "'.$data['vendorPhone'].'",
                    "variables": {"ponumber":"'.$data['ponumber'].'","vendorname":"'.$data['vendor_name'].'","vendorcode":"'.$data['vendor_code'].'","size":"'.ltrim(", ",$sizeForWhatsapp).'"},
                    "noOfVariables": 4
                }',
                CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData(["data" => 'Success', "suceess" => true]);
            return $resultJson;
        }
        return $resultJson;
        
    }
}