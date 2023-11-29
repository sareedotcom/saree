<?php

namespace Logicrays\OrderDeliveryEstimation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ConfigOption implements OptionSourceInterface
{
    /**
     * Holiday options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Monday')],
            ['value' => '2', 'label' => __('Tuesday')],
            ['value' => '3', 'label' => __('Wednesday')],
            ['value' => '4', 'label' => __('Thursday')],
            ['value' => '5', 'label' => __('Friday')],
            ['value' => '6', 'label' => __('Saturday')],
            ['value' => '0', 'label' => __('Sunday')]
        ];
    }
}
