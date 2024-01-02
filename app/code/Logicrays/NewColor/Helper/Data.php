<?php

namespace Logicrays\NewColor\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Construct function
     *
     * @param ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    public function getCustomSorting($productId) {

        $product = $this->productFactory->create()->loadByAttribute('entity_id', $productId);
        return $product->getData('sorting_for_frontend');
    }
}
