<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\ResourceModel\Codeset;

/**
 * Codeset model collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    /**
     * Id Filed
     *
     * @var \Mageants\GiftCard\Model\Codeset
     */
    //protected $_idFieldName = \Mageants\GiftCard\Model\Codeset::code_set_id;

    /**
     * Init constructor
     */
    protected function _construct()
    {
        $this->_init(\Mageants\GiftCard\Model\Codeset::class, \Mageants\GiftCard\Model\ResourceModel\Codeset::class);
    }
}
