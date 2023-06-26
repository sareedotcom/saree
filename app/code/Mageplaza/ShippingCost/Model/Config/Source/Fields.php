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
 * Class Fields
 * @package Mageplaza\ShippingCost\Model\Config\Source
 */
class Fields extends AbstractSource
{
    const COUNTRY  = 'country';
    const REGION   = 'region';
    const POSTCODE = 'postcode';

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            ''             => __('-- Please Select --'),
            self::COUNTRY  => __('Country'),
            self::REGION   => __('State/Province'),
            self::POSTCODE => __('Zip/Postcode'),
        ];
    }
}
