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

namespace Pits\GiftWrap\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;
use Pits\GiftWrap\Model\PriceCalculator;

/**
 * Class GiftWrap
 */
class GiftWrap extends DefaultTotal
{
    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var GiftWrapHelper
     */
    protected $giftWrapHelper;

    /**
     * GiftWrap constructor.
     *
     * @param Data $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param PriceCalculator $priceCalculator
     * @param GiftWrapHelper $giftWrapHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        Data $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        PriceCalculator $priceCalculator,
        GiftWrapHelper $giftWrapHelper,
        array $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
        $this->priceCalculator = $priceCalculator;
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Get array of arrays with totals information for display in PDF
     *
     * @return array
     */
    public function getTotalsForDisplay(): array
    {
        if ($this->giftWrapHelper->isModuleEnabled()) {
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
            $label = $this->giftWrapHelper->getGiftWrapFeeLabel() . ':';
            $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
            $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];

            return [$total];
        }

        return [];
    }

    /**
     * Get invoice amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->getSource()->getGiftwrapFee();
    }
}
