<?php

namespace Logicrays\VendorManagement\Model\Config\Source\Option;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
use Logicrays\VendorManagement\Model\VendorManagementFactory;

class Vendors extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @param VendorManagementFactory $vendorFactory
     */
    public function __construct(
        VendorManagementFactory $vendorFactory
    ) {
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $vendors = $this->vendorFactory->create()->getCollection();
        $this->_options[] = [
            'label' => __('Please Select'),
            'value' => '',
        ];

        foreach ($vendors as $vendor) {
            $this->_options[] = [
                    'label' => __($vendor['firstname'].' '.$vendor['lastname']),
                    'value' => $vendor['vendor_id'],
                ];
        }

        return $this->_options;
    }

    /**
     * Get all vendor
     *
     * @return array
     */
    public function getAllVendors()
    {
        $vendors = $this->vendorFactory->create()->getCollection();

        $this->_options = [];

        foreach ($vendors as $vendor) {
            $this->_options [$vendor['vendor_id']] = __($vendor['firstname'].' '.$vendor['lastname']);
        }

        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
