<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin\AuthorizeNet;

use Magento\Payment\Model\InfoInterface;

/**
 * Class SetActualAmountBeforeCapturePlugin
 */
class SetActualAmountBeforeCapturePlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * SetActualAmountBeforeCapturePlugin constructor.
     *
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param InfoInterface $payment
     * @param $amount
     * @return array
     */
    public function beforeCapture(\Magento\Payment\Model\Method\Adapter $subject, InfoInterface $payment, $amount)
    {
        if ($this->isAvailable()) {
            $amount = $this->getActualAmount();
        }

        return [$payment, $amount];
    }

    /**
     * @return float
     */
    private function getActualAmount(): float
    {
        $invoice = $this->registry->registry('current_invoice');
        if ($invoice === null) {
            return 0;
        }

        return $invoice->getBaseGrandTotal();
    }

    /**
     * @return bool
     */
    private function isAvailable(): bool
    {
        $invoice = $this->registry->registry('current_invoice');

        return $invoice && $invoice->isObjectNew();
    }
}
