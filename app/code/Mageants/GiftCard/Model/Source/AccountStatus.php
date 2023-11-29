<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class AccountStatus implements ArrayInterface
{
    /**
     * AccountStatus Option to set
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
            2 => [
                'label' => 'Expired',
                'value' => 2
            ],
            3 => [
                'label' => 'Used',
                'value' => 3
            ],
        ];
        return $options;
    }
}
