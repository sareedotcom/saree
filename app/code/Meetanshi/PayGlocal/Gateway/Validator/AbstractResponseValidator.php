<?php

namespace Meetanshi\PayGlocal\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class AbstractResponseValidator
 * @package Meetanshi\PayGlocal\Gateway\Validator
 */
abstract class AbstractResponseValidator extends AbstractValidator
{
    /**
     *
     */
    const TRANSACTION_ID = 'merchantTxnId';
    /**
     *
     */
    const RESPONSE_CODE = 'status';

    /**
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return $response[self::RESPONSE_CODE] == "SENT_FOR_CAPTURE";
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateRefundResponseCode(array $response)
    {
        return $response[self::RESPONSE_CODE] == "SENT_FOR_REFUND";
    }

    /**
     * @param array $response
     * @return bool
     */
    protected function validateTransactionId(array $response)
    {
        return isset($response[self::TRANSACTION_ID])
            && $response[self::TRANSACTION_ID] != 'null';
    }

}