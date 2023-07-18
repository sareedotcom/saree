<?php

namespace Logicrays\VendorManagement\Model\Config\Source\Option;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Catalog\Api\ProductRepositoryInterface;

class VendorsAttValue extends AbstractRenderer
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @param ProductRepositoryInterface $productRepositoryInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface
    ) {
        $this->productRepositoryInterface = $productRepositoryInterface;
    }

    /**
     * Get vendor attribute value
     *
     * @param DataObject $value
     * @return void
     */
    public function render(DataObject $value)
    {
        $productId = $value->getId();
        $productCollection = $this->productRepositoryInterface->getById($productId);

        $vendorAttr = $productCollection->getVendor();
        if ($vendorAttr) {
            return $productCollection->getAttributeText('vendor');
        }
    }
}
