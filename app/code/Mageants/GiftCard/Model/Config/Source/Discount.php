<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Config\Source;

/**
 * Discount class for return array
 */
class Discount implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Discount value set option
     *
     * @return Array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 10, 'label' => __('10')],
            ['value' => 20, 'label' => __('20')],
            ['value' => 30, 'label' => __('30')],
            ['value' => 40, 'label' => __('40')],
            ['value' => 50, 'label' => __('50')],
            ['value' => 60, 'label' => __('60')],
            ['value' => 70, 'label' => __('70')],
            ['value' => 80, 'label' => __('80')],
            ['value' => 90, 'label' => __('90')],
        ];
    }
}
