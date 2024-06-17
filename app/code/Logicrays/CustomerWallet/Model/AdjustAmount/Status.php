<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Model\AdjustAmount;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public const APPROVE_VALUE = 1;
    public const DEBIT_VALUE = 4;

    /**
     * ToOptionArray function
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::APPROVE_VALUE, 'label' => __('Approved')],
            ['value' => self::DEBIT_VALUE, 'label' => __('Debited')],
        ];
    }
}
