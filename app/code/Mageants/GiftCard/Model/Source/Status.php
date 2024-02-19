<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{

    /**
     * Status Option to set
     *
     * @return Array
     */
    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => 'Inactive',
                'value' => 0
            ],
            1 => [
                'label' => 'Active',
                'value' => 1
            ],
        ];
        return $options;
    }
}
