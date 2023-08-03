<?php

namespace Meetanshi\PayGlocal\Gateway\Validator\Direct;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Meetanshi\PayGlocal\Gateway\Validator\AbstractResponseValidator;

/**
 * Class ResponseValidator
 * @package Meetanshi\PayGlocal\Gateway\Validator\Direct
 */
class ResponseValidator extends AbstractResponseValidator
{
    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];

        $validationResult = $this->validateResponseCode($response)
            && $this->validateTransactionId($response);

        if (!$validationResult) {
            $errorMessages = [__('Something went wrong.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }
}
