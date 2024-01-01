<?php
namespace Logicrays\UpdateOrder\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeOrderStatus implements ObserverInterface
{   
    public function __construct(
        \Magento\Sales\Model\Order $order   
    ){
        $this->order = $order;     
    }

    public function execute(\Magento\Framework\Event\Observer $observer){   
        $orderId = $observer->getEvent()->getOrder()->getId();
        $order = $this->order->load($orderId);
        //   $order->setState($orderState)->setStatus(Order::STATUS_PENDING);
        //   $order->save();
        $order->setState($order->getState())->setStatus("pending_measurement");
        $order->save();
    }
}