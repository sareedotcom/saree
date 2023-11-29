<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin\AuthorizeNet;

use Magento\AuthorizenetAcceptjs\Gateway\Request\CaptureDataBuilder;

/**
 * Class CaptureActualAmountPlugin
 */
class CaptureActualAmountPlugin
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
     * @param array $result
     * @param array $buildSubject
     * @return array
     */
    public function afterBuild(CaptureDataBuilder $subject, array $result, array $buildSubject): array
    {
        if ($this->isAvailable($result)) {
            $tmpArray                     = $result['transactionRequest'];
            $resultArray                  = array_slice($tmpArray, 0, 1, true) +
                ['amount' => $this->getAmount()] +
                array_slice($tmpArray, 1, count($tmpArray) - 1, true);
            $result['transactionRequest'] = $resultArray;
        }

        return $result;
    }

    /**
     * @param array $result
     * @return bool
     */
    private function isAvailable(array $result): bool
    {
        $invoice = $this->registry->registry('current_invoice');

        if (isset($result['transactionRequest'])
            && $result['transactionRequest']['transactionType'] === 'priorAuthCaptureTransaction'
            && $invoice
            && $invoice->isObjectNew()
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return float
     */
    private function getAmount(): float
    {
        $invoice = $this->registry->registry('current_invoice');
        if ($invoice === null) {
            return 0;
        }

        return $invoice->getBaseGrandTotal();
    }
}
