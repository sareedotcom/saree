<?php

namespace Meetanshi\PayGlocal\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class DataAssignObserver
 * @package Meetanshi\PayGlocal\Observer
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const PAYGLOCAL_TOKEN = 'payglocalResponce';
    /**
     * @var array
     */
    protected $additionalInformation = [
        self::PAYGLOCAL_TOKEN
    ];

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additional = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additional)) {
            return;
        }
        $payment = $this->readPaymentModelArgument($observer);
        foreach ($this->additionalInformation as $additionalInformation) {
            $value = isset($additional[$additionalInformation])
                ? $additional[$additionalInformation]
                : null;
            if ($value === null) {
                continue;
            }
            $payment->setAdditionalInformation(
                $additionalInformation,
                $value
            );
            $payment->setData(
                $additionalInformation,
                $value
            );
        }
    }
}
