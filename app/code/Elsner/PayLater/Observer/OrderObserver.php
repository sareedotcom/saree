<?php
namespace Elsner\PayLater\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $method_instance = $observer->getEvent()->getOrder()->getPayment()->getMethodInstance();
    
        if ($method_instance->getCode() == 'paylater') {
            $order->setState("processing")->setStatus("p_payment");
            $order->save(); 
        }
    }
}