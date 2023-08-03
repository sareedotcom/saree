<?php

namespace Meetanshi\PayGlocal\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DisplayMode
 * @package Meetanshi\PayGlocal\Model\Config\Source
 */
class DisplayMode implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => "modal", 'label' => __('Modal')],
            ['value' => "inline", 'label' => __('Inline')],
            ['value' => "drawer", 'label' => __('Drawer')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "modal" => __('Modal'),
            "inline" => __('Inline'),
            "drawer" => __('Drawer'),
        ];
    }
}
