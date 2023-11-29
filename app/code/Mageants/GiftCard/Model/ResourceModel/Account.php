<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <info@mageants.com>
 */
namespace Mageants\GiftCard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Account ResourceModel class
 */
class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init resource Model
     */
    protected function _construct()
    {
        $this->_init('gift_code_account', 'account_id');
    }
}
