<?php

namespace Logicrays\UpdateOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Logicrays\UpdateOrder\Helper\Email;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

class Sendmesermentlink implements ObserverInterface
{
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param ResourceConnection $resource
     * @param ScopeConfigInterface $scopeConfig
     * @param Email $helper
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig,
        Email $helper,
        StoreManagerInterface $storeManager,
        CollectionFactory $itemCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * Execute
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $order->save();
            $items = $order->getItems();
            $isMailSend = 0;
            $connection = $this->_resource->getConnection();
            $dateArr = [];
            $minDate = "";
            foreach ($items as $item) {
                 
                $select = $connection->select()
                    ->from(
                        ['main' => 'sales_order_item'],
                        ['estd_dispatch_date']
                    )
                    ->where(
                        "main.item_id = :item_id"
                    )
                    ->where(
                        "main.estd_dispatch_date != ''"
                    );
                $bind = ['item_id' => $item->getId()];
                $result = $connection->fetchRow($select, $bind);
                if(isset($result['estd_dispatch_date'])){
                    $dateForInd = date_format(date_create($result['estd_dispatch_date']),"Y-m-d");
                    $dateArr[] =  $dateForInd;
                }
                $dateForDisplay = "";
                if(count($dateArr)){
                    $minDate = min($dateArr);
                    if($minDate){
                        $dateForDisplay = $minDate;
                    }
                }
                
                $options = $item->getProductOptions();        
                if (isset($options['options']) && !empty($options['options'])) {        
                    foreach ($options['options'] as $option) {
                        if($option['print_value'] == "Later"){

                            $data['order_id'] = $order->getId();
                            $data['customerEmail'] = $order->getCustomerEmail();
                            $data['customerName'] = $order->getCustomerName();
                            $data['incrementId'] = $order->getIncrementId();
                            $isMailSend = 1;
                            $orderItem = $this->itemCollectionFactory->create()->addFieldToFilter( 'item_id', $item->getId())->getFirstItem();
                            $orderItem->setLrItemStatus('pending_measurement');
                            $orderItem->save();
                        }
                        else if($option['print_value'] == "Yes"){
                            $orderItem = $this->itemCollectionFactory->create()->addFieldToFilter( 'item_id', $item->getId())->getFirstItem();
                            $orderItem->setLrItemStatus('measurement_submitted');
                            $orderItem->save();
                        }
                        else if($option['print_value'] == "No"){
                            $orderItem = $this->itemCollectionFactory->create()->addFieldToFilter( 'item_id', $item->getId())->getFirstItem();
                            $orderItem->setLrItemStatus('measurement_not_required');
                            $orderItem->save();
                        }
                    }
                }
            }
            if($minDate){
                $sql = "UPDATE `sales_order_grid` SET `nearestdispatchnew` = '".$dateForDisplay. " 00:00:01' WHERE `increment_id` = ".$order->getIncrementId();
                $connection->query($sql);
            }
            if($isMailSend){
                $order->setState($order->getState())->setStatus("pending_measurement");
                $order->save();
                $this->helper->sendEmail($data);
                $orderLink = $this->_storeManager->getStore()->getBaseUrl()."sales/order/view/order_id/".$order->getId();
                
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://saree.bobot.in/workflow/webhook/935d1abd-191d-4621-994b-ae8f78a03f5e',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                        "platform": "saree",
                        "phone": "'.$order->getShippingAddress()->getTelephone().'",
                        "variables": {"customer_name":"'.$order->getCustomerName().'","messerment_link":"'.$orderLink.'"},
                        "noOfVariables": 2
                    }',
                    CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
        
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}