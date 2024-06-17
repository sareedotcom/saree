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

namespace Logicrays\CustomerWallet\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    public const PENDING_VALUE = 0;
    public const CREDIT_VALUE = 1;
    public const CANCEL_VALUE = 2;
    public const DEBIT_VALUE = 4;

    /**
     * ToOptionArray function
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PENDING_VALUE, 'label' => __('Pending')],
            ['value' => self::CREDIT_VALUE, 'label' => __('Credited')],
            ['value' => self::CANCEL_VALUE, 'label' => __('Canceled')],
            ['value' => self::DEBIT_VALUE, 'label' => __('Debited')],
        ];
    }
}
