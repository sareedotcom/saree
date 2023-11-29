<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Edit;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Catalog\Model\Product as ProductModel;

/**
 * Class Thumbnail
 */
class Thumbnail
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Thumbnail constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productResource   = $productResource;
        $this->productFactory    = $productInterfaceFactory;
        $this->imageHelper       = $imageHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param OrderItemInterface $item
     * @return \Magento\Catalog\Helper\Image|bool
     */
    public function getImgByItem(OrderItemInterface $item)
    {
        try {
            /** @var ProductInterface|ProductModel $product */
            if ($item->getProductType() == Configurable::TYPE_CODE) {
                $product = $this->productRepository->get($item->getSku());
            } else {
                $product = $item->getProduct();
                if ($product === null) {
                    throw new NoSuchEntityException(__('No such product for item with SKU %1', $item->getSku()));
                }
                $product->setSku($item->getSku());
            }
            $product->setStoreId($item->getStoreId());

            if (!$product->getThumbnail() || $product->getThumbnail() == 'no_selection') {
                return false;
            }

            return $this->imageHelper->init($product, 'product_listing_thumbnail');
        } catch (NoSuchEntityException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
