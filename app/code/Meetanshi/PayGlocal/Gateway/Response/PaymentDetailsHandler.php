<?php

namespace Meetanshi\PayGlocal\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Meetanshi\PayGlocal\Gateway\Validator\AbstractResponseValidator;

/**
 * Class PaymentDetailsHandler
 * @package Meetanshi\PayGlocal\Gateway\Response
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        $payment->setTransactionId($response[AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setLastTransId($response[AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(false);

        $payment->setAdditionalInformation('transaction_id', $response[AbstractResponseValidator::TRANSACTION_ID]);
        $payment->setAdditionalInformation('status', $response['status']);
    }
}
