<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Logicrays\PaymentLink\Observer\Email;

use Magento\Customer\Api\CustomerRepositoryInterface;

class OrderSetTemplateVarsBefore implements \Magento\Framework\Event\ObserverInterface {

    public function __construct(
        \Logicrays\PaymentLink\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $transport = $observer->getTransport();
        $transportord = $observer->getEvent()->getTransport();
        $order = $transportord->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $paymentMethod = $method->getTitle();
        if($paymentMethod == "Cash On Delivery"){
            $stripePaymentLink = $this->helperData->sendStipePaymentLink("INR", $order->getGrandTotal());
            $rozorPaymentLink = $this->helperData->sendRozorPaymentLink($order->getIncrementId(), $order->getGrandTotal(), $order->getCustomerName(), $order->getBillingAddress()->getTelephone(), $order->getCustomerEmail());
            $transport['stripePaymentLink'] = $stripePaymentLink;
            $transport['rozorPaymentLink'] = $rozorPaymentLink;
        }
    }
} 