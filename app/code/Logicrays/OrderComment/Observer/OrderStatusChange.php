<?php

namespace Logicrays\OrderComment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Auth\Session;

class OrderStatusChange implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * Auto generate comment when status on hold
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return string
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getState() == 'holded' && $order->getStatus() == 'holded') {
            $adminUsername = $this->session->getUser()->getUsername();
            $comment = "<b>(By : ".$adminUsername.")</b>";
            $order->addStatusHistoryComment($comment)
                ->setIsCustomerNotified(false)  // Set to true if you want to notify the customer
                ->save();
        }
    }
}
