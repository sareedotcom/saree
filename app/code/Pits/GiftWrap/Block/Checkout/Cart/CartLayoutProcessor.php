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

namespace Pits\GiftWrap\Block\Checkout\Cart;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Pits\GiftWrap\Helper\Data;

/**
 * Class CartLayoutProcessor
 */
class CartLayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var Data
     */
    private $giftWrapHelper;

    /**
     * CartLayoutProcessor constructor.
     *
     * @param Data $giftWrapHelper
     * @return void
     */
    public function __construct(
        Data $giftWrapHelper
    ) {
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Enable or disable gift wrap section on cart summery
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->giftWrapHelper->isModuleEnabled()) {
            unset($jsLayout['components']['block-totals']['children']['fee']);
        }

        return $jsLayout;
    }
}
