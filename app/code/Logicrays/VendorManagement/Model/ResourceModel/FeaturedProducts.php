<?php

namespace Logicrays\VendorManagement\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * FeaturedProducts resource model
 */
class FeaturedProducts extends AbstractDb
{
    public const ENTITY_NAME = "logicrays_vendor_products";

    public const ENTITY_ID = "id";

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::ENTITY_NAME, self::ENTITY_ID);
    }
}
