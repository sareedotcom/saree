<?php
namespace Logicrays\VendorManagement\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Logicrays\VendorManagement\Helper\Data;

class VendorProductUnassigned implements ObserverInterface
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
     * Call an API to product delete from ERP after delete product from Magento
     *
     * @param   Observer $observer
     * @return  $this
     */
    public function execute(Observer $observer)
    {
        $eventProduct = $observer->getEvent()->getProduct();
        $productId = $eventProduct->getId();
        if ($productId) {
            $checkIfProductVendorAlreadyAssigned = $this->helper->getAlreadyAssignedProduct($productId);
            if (!empty($checkIfProductVendorAlreadyAssigned)) {
                $assignedData = $checkIfProductVendorAlreadyAssigned[0];
                $this->helper->deleteVendorProduct($assignedData);
            }
        }
        return $this;
    }
}
