<?php
namespace Logicrays\BookACall\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Logicrays\BookACall\Helper\Email;

class SendBookACallMail implements ObserverInterface
{   
    /**
     * @param Order $scopeConfig
     * @param Email $helper
     */
    public function __construct    (             
        Order $order,
        Email $helper
    ) 
    {        
        $this->order = $order;  
        $this->helper = $helper;   
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $order = $observer->getEvent()->getOrder();
        $order->save();
        $items = $order->getItems();
        $isMailSend = 0;
        $data = [];
        foreach ($items as $item) {
            $options = $item->getProductOptions();        
            if (isset($options['options']) && !empty($options['options'])) {
                foreach ($options['options'] as $option) {
                    if($option['label'] == "Country"){
                        $data['order_id'] = $order->getIncrementId();
                        $data['customerEmail'] = $order->getCustomerEmail();
                        $data['customerName'] = $order->getCustomerName();
                        $data['incrementId'] = $order->getIncrementId();
                        $data['country'] = $option['value'];
                        $isMailSend = 1;
                    }
                    else if($option['label'] == "Name"){
                        $data['name'] = $option['value'];
                    }
                    else if($option['label'] == "Whatsapp Number"){
                        $data['number'] = $option['value'];
                    }
                    else if($option['label'] == "Outfits & Occasion"){
                        $data['outfitsandoccasion'] = $option['value'];
                    }
                    
                }
            }
        }
        if($isMailSend){
            $this->helper->sendEmail($data);
        }
    }
}