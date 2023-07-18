<?php

namespace Logicrays\VendorManagement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Logicrays\VendorManagement\Helper\Data;

class UpdateVendorProduct implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Update vendor product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $checkIfProductVendorAlreadyAssigned = $this->helper->getAlreadyAssignedProduct($product->getId());
        if (!empty($checkIfProductVendorAlreadyAssigned)) {
            $assignedData = $checkIfProductVendorAlreadyAssigned[0];
            if ($product->getVendor()) {
                $this->helper->updateAlreadyAssignedProduct($assignedData, $product->getVendor());
            } else {
                $this->helper->deleteVendorProduct($assignedData);
            }

        } else {
            $this->helper->updateVendorProduct($product->getId(), $product->getVendor());
        }
    }
}
