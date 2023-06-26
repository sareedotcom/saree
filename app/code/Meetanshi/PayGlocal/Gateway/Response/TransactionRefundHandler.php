<?php

namespace Meetanshi\PayGlocal\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class TransactionRefundHandler
 * @package Meetanshi\PayGlocal\Gateway\Response
 */
class TransactionRefundHandler implements HandlerInterface
{
    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $orderPayment = $paymentDO->getPayment();
        $orderPayment->setTransactionId($response['data']['merchantTxnId']);
        $orderPayment->setIsTransactionClosed(true);
        $orderPayment->setShouldCloseParentTransaction(!$orderPayment->getCreditmemo()->getInvoice()->canRefund());
    }
}
