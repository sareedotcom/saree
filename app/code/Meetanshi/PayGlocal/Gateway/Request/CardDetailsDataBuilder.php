<?php

namespace Meetanshi\PayGlocal\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Meetanshi\PayGlocal\Helper\Data;

/**
 * Class CardDetailsDataBuilder
 * @package Meetanshi\PayGlocal\Gateway\Request
 */
class CardDetailsDataBuilder implements BuilderInterface
{
    /**
     *
     */
    const AMOUNT = 'amount';
    /**
     *
     */
    const PAYGLOCAL_TOKEN = 'payglocalResponce';

    /**
     * @var SubjectReader
     */
    private $subjectReader;
    /**
     * @var Data
     */
    private $helper;

    /**
     * CardDetailsDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @param Data $helper
     */
    public function __construct(
        SubjectReader $subjectReader,
        Data $helper
    )
    {
        $this->subjectReader = $subjectReader;
        $this->helper = $helper;
    }

    /**
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();

        $result = [
            self::PAYGLOCAL_TOKEN => $payment->getAdditionalInformation(self::PAYGLOCAL_TOKEN)
        ];
        return $result;
    }
}

