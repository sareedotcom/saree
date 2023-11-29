<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Position implements ArrayInterface
{
    /**
     * Position Option to set
     *
     * @return Array
     */
    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => 'Top-Left',
                'value' => 0
            ],
            1 => [
                'label' => 'Top-Center',
                'value' => 1
            ],
            2 => [
                'label' => 'Top_Right',
                'value' => 2
            ],
            3 => [
                'label' => 'Center-Left',
                'value' => 3
            ],
            4 => [
                'label' => 'Center',
                'value' => 4
            ],
            5 => [
                'label' => 'Center-Right',
                'value' => 5
            ],
            6 => [
                'label' => 'Bottm-Left',
                'value' => 6
            ],
            7 => [
                'label' => 'Bottm-Center',
                'value' => 7
            ],
            8 => [
                'label' => 'Bottm-Right',
                'value' => 8
            ],
        ];
        return $options;
    }
}
