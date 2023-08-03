<?php

namespace Logicrays\VendorManagement\Model\ResourceModel\VendorManagement;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'vendor_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Logicrays\VendorManagement\Model\VendorManagement::class,
            \Logicrays\VendorManagement\Model\ResourceModel\VendorManagement::class
        );
    }
}
