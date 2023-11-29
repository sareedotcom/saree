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

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pits\GiftWrap\Helper\Data;
use Pits\GiftWrap\Model\PriceCalculator;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class AbstractGiftWrapTotals
 */
abstract class AbstractGiftWrapTotals extends Template
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var Data
     */
    protected $giftWrapHelper;

    /**
     * AbstractGiftWrapTotals constructor.
     *
     * @param Context $context
     * @param Currency $currency
     * @param PriceCalculator $priceCalculator
     * @param Data $giftWrapHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        Currency $currency,
        PriceCalculator $priceCalculator,
        Data $giftWrapHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
        $this->priceCalculator = $priceCalculator;
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Get source
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol(): string
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * Add gift wrap fee to the totals
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->giftWrapHelper->isModuleEnabled() && $this->getGiftWrapFee(true)>0) {
            $this->getParentBlock();
            $this->getModel();
            $this->getSource();
            $totals = new DataObject(
                [
                    'code'       => Wrap::GIFT_WRAP_FEE_IDENTIFIER,
                    'label'      => $this->giftWrapHelper->getGiftWrapFeeLabel(),
                    'value'      => $this->getGiftWrapFee(true),
                    'base_value' => $this->getGiftWrapFee(),
                ]
            );
            $this->getParentBlock()->addTotalBefore($totals, 'grand_total');
        }

        return $this;
    }
}
