<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 *
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

namespace Pits\GiftWrap\Model\Total;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Pits\GiftWrap\Model\PriceCalculator;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class Fee
 *
 * @package Pits\GiftWrap\Model\Total
 */
class Fee extends AbstractTotal
{
    /**
     * Reset amount
     */
    const RESET_AMOUNT = 0;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var GiftWrapHelper
     */
    private $giftWrapHelper;
    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * Fee constructor.
     *
     * @param PriceCalculator $priceCalculator
     * @param Data $priceHelper
     * @param GiftWrapHelper $giftWrapHelper
     * @param Wrap $wrap
     * @return void
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        Data $priceHelper,
        GiftWrapHelper $giftWrapHelper,
        Wrap $wrap
    ) {
        $this->priceCalculator = $priceCalculator;
        $this->priceHelper = $priceHelper;
        $this->giftWrapHelper = $giftWrapHelper;
        $this->wrap = $wrap;
    }

    /**
     * Collect totals
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|Fee
     * @throws LocalizedException
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if ($this->giftWrapHelper->isModuleEnabled()) {
            $giftWrapFee = 0;
            $storeGiftWrapFee = 0;
            if ($quote->getIsMultiShipping()) {
                foreach ($quote->getAllShippingAddresses() as $address) {
                    $allCartGiftActive = false;
                    if ($this->_getAddress()->getId() == $address->getId()) {
                        $itemIds = array();
                        $giftWrap = $this->wrap->getQuoteItemGiftWrapData($quote);
                        if ($giftWrap) {
                            $allCartGiftActive = true;
                            $itemIds[] = $giftWrap->getId();
                        }

                        if(count($address->getAllVisibleItems()) > 1 || !$allCartGiftActive)
                        {
                            foreach ($address->getAllVisibleItems() as $value) {
                                $giftWrap = $this->wrap->getQuoteItemGiftWrapData($quote, $value->getQuoteItemId());
                                if ($giftWrap) {
                                    $itemIds[] = $giftWrap->getId();
                                }
                            }
                        }
                        $giftWrapFee = $this->priceCalculator->getQuoteAddressGiftWrapFee($itemIds);
                        $storeGiftWrapFee = $this->priceHelper->currency($giftWrapFee, false, false);
                    }
                }
            }else
            {
                $giftWrapFee = $this->priceCalculator->getQuoteGiftWrapFee();
                $storeGiftWrapFee = $this->priceHelper->currency($giftWrapFee, false, false);
            }

            $total->setTotalAmount(Wrap::GIFT_WRAP_FEE_IDENTIFIER, $storeGiftWrapFee);
            $total->setBaseTotalAmount(Wrap::GIFT_WRAP_FEE_IDENTIFIER, $giftWrapFee);
            $total->setData(Wrap::GIFT_WRAP_FEE_IDENTIFIER, $giftWrapFee);
            $total->setGiftwrapFee($storeGiftWrapFee);
            $total->setBaseGiftwrapFee($giftWrapFee);
            $total->setGrandTotal($total->getGrandTotal());
            $total->setBaseGrandTotal($total->getBaseGrandTotal());
        }

        return $this;
    }

    /**
     * Clear all total values
     *
     * @param Total $total
     * @return void
     */
    protected function clearValues(Total $total)
    {
        $total->setTotalAmount('subtotal', self::RESET_AMOUNT);
        $total->setBaseTotalAmount('subtotal', self::RESET_AMOUNT);
        $total->setTotalAmount('tax', self::RESET_AMOUNT);
        $total->setBaseTotalAmount('tax', self::RESET_AMOUNT);
        $total->setTotalAmount('discount_tax_compensation', self::RESET_AMOUNT);
        $total->setBaseTotalAmount('discount_tax_compensation', self::RESET_AMOUNT);
        $total->setTotalAmount('shipping_discount_tax_compensation', self::RESET_AMOUNT);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', self::RESET_AMOUNT);
        $total->setSubtotalInclTax(self::RESET_AMOUNT);
        $total->setBaseSubtotalInclTax(self::RESET_AMOUNT);
    }

    /**
     * Fetch fee data
     *
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        $giftWrapTotalRenderer = [];
        if ($quote->getIsMultiShipping()) {
            $giftWrapTotalRenderer = [
                'code'       => Wrap::GIFT_WRAP_FEE_IDENTIFIER,
                'title'      => $this->getLabel(),
                'base_value' => $total->getBaseGiftwrapFee(),
                'value'      => $total->getGiftwrapFee(),
            ];
        }else{
            $giftWrapTotalRenderer = [
                'code'       => Wrap::GIFT_WRAP_FEE_IDENTIFIER,
                'title'      => Wrap::GIFT_WRAP_FEE_IDENTIFIER,
                'base_value' => $this->priceCalculator->getQuoteGiftWrapFee(),
                'value'      => $this->priceHelper->currency($this->priceCalculator->getQuoteGiftWrapFee(), false, false),
            ];
        }

        return $giftWrapTotalRenderer;
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase
     */
    public function getLabel()
    {
        return __('Gift Wrap Fee');
    }
}
