<?php

namespace Meetanshi\PayGlocal\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class RefundValidator
 * @package Meetanshi\PayGlocal\Gateway\Validator
 */
class RefundValidator extends AbstractResponseValidator
{
    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $errorMessages = [];

        $validationResult = $this->validateRefundResponseCode($response);

        if (!$validationResult) {
            $errorMessages = [__((string)$response['message'])];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
