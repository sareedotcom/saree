<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingCost
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingCost\Model\Config\Source;

/**
 * Class Country
 * @package Mageplaza\ShippingCost\Model\Config\Source
 */
class Country extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     *
     * @return array
     */
    public function toOptionArray($isMultiselect = true, $foregroundCountries = '')
    {
        $options = parent::toOptionArray($isMultiselect, $foregroundCountries);

        array_unshift($options, ['value' => '', 'label' => __('All')]);

        return $options;
    }
}
