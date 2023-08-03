<?php

namespace Logicrays\VendorManagement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class QuoteAddItemObserver implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param ProductRepository $productRepository
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        ProductRepository $productRepository,
        RequestInterface $request,
        Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->_request = $request;
        $this->helper = $helper;
    }

    /**
     * Set order item vendor
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->_request->getParam('product');
        if ($product) {
            if ($this->_request->getFullActionName() == 'checkout_cart_add') {
                $items = $observer->getEvent()->getData('items');
                foreach ($items as $item) {
                    $product = $this->productRepository->getById($item->getProductId());
                    $vendorAttribute = $product->getResource()->getAttribute('vendor');
                    if ($product->getVendor()) {
                        $item->setOrderItemVendor($vendorAttribute->getFrontend()->getValue($product)->getText());
                    }
                }
            }
        } else {
            if ($this->_request->getFullActionName() == 'sales_order_reorder') {
                if ($this->_request->getParam('order_id')) {
                    $items = $observer->getEvent()->getData('items');
                    foreach ($items as $item) {
                        $product = $this->productRepository->getById($item->getProductId());
                        $vendorAttribute = $product->getResource()->getAttribute('vendor');
                        if ($product->getVendor()) {
                            $item->setOrderItemVendor($vendorAttribute->getFrontend()->getValue($product)->getText());
                            $item->save();
                        }
                    }
                }
            }
        }
    }
}
