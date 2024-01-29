<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Logicrays\UpdateOrder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Sales\Model\OrderRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Logicrays\VendorManagement\Model\VendorManagementFactory;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Data constructor.
     *
     * @param Context $context
     * @param OrderStatusCollection $orderStatusCollection,
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        OrderStatusCollection $orderStatusCollection,
        OrderRepository $orderRepository,
        Product $product,
        ProductRepositoryInterface $productRepository,
        VendorManagementFactory $vendorFactory
    ) {
        $this->orderStatusCollection=$orderStatusCollection;
        $this->orderRepository = $orderRepository;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * Get Order Status
     * @return array
     */
    public function getOrderStatus($orderId)
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $order = $this->orderRepository->get($orderId);
        $allStatus = $this->orderStatusCollection->toOptionArray(); // All Status
        $statusList = [];
        foreach ($allStatus as $key => $value) {
            $statusList[$value['value']] = $value['label'];
        }
        return $statusList;
    }

    /**
     * Get product
     *
     * @param int $productSku
     * @return void
     */
    public function getProduct($productSku)
    {
        return $this->productRepository->get($productSku);
    }

    /**
     * Save product cost
     *
     * @param int $productId $productCost
     * @return void
     */
    public function saveProductCost($productSku, $productCost)
    {
        $product = $this->productRepository->get($productSku);

        if($product) {
            $product->setData('cost', $productCost);
            $this->productRepository->save($product);
        }
    }


    /**
     * Get all vendor
     *
     * @return array
     */
    public function getAllVendors()
    {
        $vendors = $this->vendorFactory->create()->getCollection()->getData();

        $vendorList = [];

        foreach ($vendors as $key => $vendor) {
            $vendorList[$vendor['vendor_id']] = __($vendor['firstname'].' '.$vendor['lastname']);
        }

        return $vendorList;
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
}
