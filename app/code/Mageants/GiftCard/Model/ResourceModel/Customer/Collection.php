<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\ResourceModel\Customer;

/**
 * Customer model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init constructor
     */
    protected function _construct()
    {
        $this->_init(
            \Mageants\GiftCard\Model\Customer::class,
            \Mageants\GiftCard\Model\ResourceModel\Customer::class
        );
    }
}
