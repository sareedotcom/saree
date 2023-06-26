<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\ShippingCost\Block\Product;

use Mageplaza\ShippingCost\Model\Config\Source\Position;

/**
 * Class Tab
 * @package Mageplaza\ShippingCost\Block\Product
 */
class Tab extends AbstractForm
{
    const POSITION = Position::ADDITIONAL_TAB;

    /**
     * @return $this
     */
    public function setTabData()
    {
        $title = $this->helper->getTitle() ?: __('Shipping Calculator');

        $this->setData('title', $title)->setData('sort_order', 100);

        return $this;
    }
}
