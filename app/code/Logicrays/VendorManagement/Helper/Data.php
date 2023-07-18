<?php

namespace Logicrays\VendorManagement\Helper;

use Magento\Framework\App\ResourceConnection;
use Logicrays\VendorManagement\Model\VendorManagementFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const VENDOR_PRODUCT_TABLE = 'logicrays_vendor_products';

    /**
     * @var VendorManagementFactory
     */
    protected $vendorFactory;

    /**
     * Undocumented function
     *
     * @param ResourceConnection $resource
     * @param VendorManagementFactory $vendorFactory
     */
    public function __construct(
        ResourceConnection $resource,
        VendorManagementFactory $vendorFactory
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * Get vendor
     *
     * @param int $vendorId
     * @return void
     */
    public function getVendor($vendorId)
    {
        return $this->vendorFactory->create()->load($vendorId);
    }

    /**
     * Return already assigned products
     *
     * @param [type] $productId
     * @return array
     */
    public function getAlreadyAssignedProduct($productId)
    {
        $tbl = self::VENDOR_PRODUCT_TABLE;
        $select = $this->resource->getConnection()->select()->from(
            $tbl
        )
        ->where(
            'product_id = ?',
            $productId
        );

        return $this->resource->getConnection()->fetchAll($select);
    }

    /**
     * Update already assigned product
     *
     * @param array $assignedData
     * @param int $vendorId
     * @return array
     */
    public function updateAlreadyAssignedProduct($assignedData, $vendorId)
    {
        $table = self::VENDOR_PRODUCT_TABLE;
        $where = ['vendor_id = ?' => $assignedData['vendor_id'], 'product_id IN (?)' => $assignedData['product_id']];
        $this->connection->update($table, ['vendor_id' => $vendorId], $where);
    }

    /**
     * Update assigned product
     *
     * @param int $productId
     * @param int $vendorId
     * @return array
     */
    public function updateVendorProduct($productId, $vendorId)
    {
        $table = self::VENDOR_PRODUCT_TABLE;
        $data = ['vendor_id' => $vendorId, 'product_id' => (int) $productId];
        $this->connection->insert($table, $data);
    }

    /**
     * Delete assigned product
     *
     * @param array $assignedData
     * @return array
     */
    public function deleteVendorProduct($assignedData)
    {
        $table = self::VENDOR_PRODUCT_TABLE;
        $where = ['vendor_id = ?' => $assignedData['vendor_id'], 'product_id = ?' => $assignedData['product_id']];
        $this->connection->delete($table, $where);
    }
}
