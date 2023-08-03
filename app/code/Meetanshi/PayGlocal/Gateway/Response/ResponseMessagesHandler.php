<?php

namespace Meetanshi\PayGlocal\Gateway\Response;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class ResponseMessagesHandler
 * @package Meetanshi\PayGlocal\Gateway\Response
 */
class ResponseMessagesHandler implements HandlerInterface
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

        foreach ($response as $key => $value) {
            $payment->setAdditionalInformation(
                $key,
                $value
            );
        }
    }
}
