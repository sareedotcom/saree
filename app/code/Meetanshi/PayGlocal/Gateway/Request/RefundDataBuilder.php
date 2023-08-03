<?php

namespace Meetanshi\PayGlocal\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Meetanshi\PayGlocal\Helper\Data;

/**
 * Class RefundDataBuilder
 * @package Meetanshi\PayGlocal\Gateway\Request
 */
class RefundDataBuilder implements BuilderInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * RefundDataBuilder constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $gid = $payment->getAdditionalInformation("gid");
        $amount = SubjectReader::readAmount($buildSubject);

        $refund = 'P';
        if ($amount == $order->getGrandTotalAmount()) {
            $refund = 'F';
        }

        $payload = json_encode([
            "merchantTxnId" => $this->helper->generateRandomString(19),
            "refundType" => $refund,
            "paymentData" => array(
                "totalAmount" => round($amount, 2),
                "txnCurrency" => $order->getCurrencyCode()
            )
        ]);

        return [
            "payload" => $payload,
            "gid" => $gid
        ];
    }
}
