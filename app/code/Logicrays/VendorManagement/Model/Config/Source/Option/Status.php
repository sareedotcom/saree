<?php

namespace Logicrays\VendorManagement\Model\Config\Source\Option;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Pending')], ['value' => 1, 'label' => __('Active')],
               ['value' => 2, 'label' => __('In Active')]];
    }
}
