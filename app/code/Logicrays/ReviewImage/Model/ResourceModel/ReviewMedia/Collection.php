<?php
namespace Logicrays\ReviewImage\Model\ResourceModel\ReviewMedia;

/**
 * Class Collection
 *
 * @package Logicrays\ReviewImage\Model\ResourceModel\ReviewMedia
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * constructor
     *
     */
    protected function _construct()
    {
        $this->_init('Logicrays\ReviewImage\Model\ReviewMedia', 'Logicrays\ReviewImage\Model\ResourceModel\ReviewMedia');
    }
}
