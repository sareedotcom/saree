<?php

namespace Logicrays\VendorManagement\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Grid Grid mysql resource.
 */
class VendorManagement extends AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('logicrays_vendor', 'vendor_id');
    }
}
