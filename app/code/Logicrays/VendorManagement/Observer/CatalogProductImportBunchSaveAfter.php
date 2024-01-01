<?php

namespace Logicrays\VendorManagement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CatalogProductImportBunchSaveAfter implements ObserverInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param ResourceConnection $resource
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ResourceConnection $resource,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_resource = $resource;
        $this->productRepository = $productRepository;
    }

    /**
     * Update vendor product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $connection = $this->_resource->getConnection();

        $lvTable = $this->_resource->getTableName('logicrays_vendor');
        $lrVendorProduct = $this->_resource->getTableName('logicrays_vendor_products');
        try {
            $bunch = $observer->getBunch();
            foreach($bunch as $product) {
                $vendorId = 0;
                $select = $connection->select()
                    ->from(
                        ['main_table' => $lvTable],
                        ['vendor_id']
                    )
                    ->where("main_table.firstname LIKE ?","%".trim($product['vendor'])."%");
                $vendorId = $connection->fetchOne($select);
                if($vendorId){
                    $product = $this->productRepository->get($product['sku']);
                    $selectVP = $connection->select()
                    ->from(
                        ['main_table' => $lrVendorProduct],
                        ['id']
                    )->where("main_table.product_id = ?",$product->getId());
                    $isAssign = $connection->fetchOne($selectVP);
                    if($isAssign){
                        $write = "Update " . $lrVendorProduct . " Set vendor_id = ".$vendorId." WHERE product_id = ".$product->getId();
                        $connection->query($write);
                    }
                    else{
                        $tableColumn = ['vendor_id', 'product_id'];
                        $tableData[] = [$vendorId, $product->getId()];
                        $connection->insertArray($lrVendorProduct, $tableColumn, $tableData);
                    }
                }
            }
        }
        catch (\Execption $e) {
        }
    }
}
