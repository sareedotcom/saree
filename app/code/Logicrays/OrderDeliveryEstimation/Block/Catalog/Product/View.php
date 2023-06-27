<?php

namespace Logicrays\OrderDeliveryEstimation\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\Registry;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class View extends AbstractProduct
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        array $data
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get current product
     *
     * @return Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get order delivery estimation date
     *
     * @return string
     */
    public function getDeliveryDay()
    {
        $currentProduct = $this->registry->registry('current_product');
        $deliveryEstimationDate = $this->helper->getDeliveryEstimationDate($currentProduct);
        return $deliveryEstimationDate;
    }
}
