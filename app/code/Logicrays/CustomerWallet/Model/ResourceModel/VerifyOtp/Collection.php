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

namespace Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * _construct function
     *
     * @return array
     */
    protected function _construct()
    {
        $this->_init(
            \Logicrays\CustomerWallet\Model\VerifyOtp::class,
            \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp::class
        );
    }
}
