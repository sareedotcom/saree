<?php

namespace Elsner\Multicurrency\Model\Order\Payment\State;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusResolver;

class RegisterCaptureNotificationCommand
{
    
    public function afterExecute(\Magento\Sales\Model\Order\Payment\State\RegisterCaptureNotificationCommand $subject, $result, $payment, $amount, OrderInterface $order)
    {
        $message = 'Registered notification about captured amount of %1.';

        if ($payment->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'An amount of %1 will be captured after being approved at the payment gateway.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $status = Order::STATUS_FRAUD;
            $message = 'Order is suspended as its capture amount %1 is suspected to be fraudulent.';
        }

        return __($message, $order->getOrderCurrency()->formatTxt($amount));
    }

    
}
