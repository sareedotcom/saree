<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

namespace Pits\GiftWrap\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;

/**
 * Class GiftWrapTotals
 *
 * @package Pits\GiftWrap\Block\Adminhtml\Sales\Order
 */
class GiftWrapTotals extends AbstractGiftWrapTotals
{
    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getModel(): Order
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get gift wrap fee
     *
     * @paarm bool $store
     * @return float
     */
    public function getGiftWrapFee($store = false)
    {
        return $this->priceCalculator->getOrderGiftWrapFeeFromQuote($this->getModel(), $store);
    }
}
