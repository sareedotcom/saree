<?php

namespace Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * FeaturedProducts collection
 */
class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Logicrays\VendorManagement\Model\FeaturedProducts::class,
            \Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts::class
        );
    }
}
