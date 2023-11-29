<?php
namespace Test\Extension\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeOrderStatus implements ObserverInterface
{   
    const MEASUREMENT_PENDING = "measurement_pending";

    public function __construct    (             
    \Magento\Sales\Model\Order $order   
    ) 
    {        
        $this->order = $order;     
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $orderId = $observer->getEvent()->getOrder()->getId();
        $order = $this->order->load($orderId); 
        // $orderState = Order::STATUS_PENDING;
        // $order->setState($orderState)->setStatus(Self::MEASUREMENT_PENDING);
        // $order->save();
    }
}