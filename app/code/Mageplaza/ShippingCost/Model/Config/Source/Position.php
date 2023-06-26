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
 * Class Position
 * @package Mageplaza\ShippingCost\Model\Config\Source
 */
class Position extends AbstractSource
{
    const AFTER_PRODUCT  = 'after_product';
    const ADDITIONAL_TAB = 'additional_tab';

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            ''                   => __('-- Please Select --'),
            self::AFTER_PRODUCT  => __('Below Product Description'),
            self::ADDITIONAL_TAB => __('In Additional Tab'),
        ];
    }
}
