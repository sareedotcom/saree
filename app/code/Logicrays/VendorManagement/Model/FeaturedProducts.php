<?php

namespace Logicrays\VendorManagement\Model;

use Magento\Framework\Model\AbstractModel;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * FeaturedProducts Model
 */
class FeaturedProducts extends AbstractModel implements IdentityInterface
{

    /**
     * CMS page cache tag
     */
    public const CACHE_TAG = 'vendor_products_grid';

    /**
     * Define model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get products
     *
     * @param int $vendorId
     * @return array
     */
    public function getProducts($vendorId)
    {
        $tbl = $this->getResource()->getTable(
            \Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts::ENTITY_NAME
        );
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['product_id']
        )
        ->where(
            'vendor_id = ?',
            (int)$vendorId
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

    /**
     * Return already assigned productss
     *
     * @param [type] $insert
     * @return array
     */
    public function getAlreadyAssignedProducts($insert)
    {
        $tbl = $this->getResource()->getTable(
            \Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts::ENTITY_NAME
        );
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl
        )
        ->where(
            'product_id IN (?)',
            $insert
        );

        return $this->getResource()->getConnection()->fetchAll($select);
    }
}
