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

namespace Pits\GiftWrap\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Creditmemo;
use Pits\GiftWrap\Helper\Data;
use Pits\GiftWrap\Model\GiftWrapData;
use Pits\GiftWrap\Model\PriceCalculator;
use Pits\GiftWrap\Block\Adminhtml\Sales\Order\AbstractGiftWrapTotals;

/**
 * Class GiftWrapTotals
 */
class GiftWrapTotals extends AbstractGiftWrapTotals
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * GiftWrapTotals constructor.
     *
     * @param Context $context
     * @param Currency $currency
     * @param PriceCalculator $priceCalculator
     * @param Data $giftWrapHelper
     * @param GiftWrapData $giftWrapData
     * @param RequestInterface $request
     * @return void
     */
    public function __construct(
        Context $context,
        Currency $currency,
        PriceCalculator $priceCalculator,
        Data $giftWrapHelper,
        GiftWrapData $giftWrapData,
        RequestInterface $request
    ) {
        parent::__construct($context, $currency, $priceCalculator, $giftWrapHelper);
        $this->request = $request;
        $this->giftWrapData = $giftWrapData;
    }

    /**
     * Retrieve current creditmemo model instance
     *
     * @return Creditmemo
     */
    public function getModel(): Creditmemo
    {
        return $this->getParentBlock()->getCreditmemo();
    }

    /**
     * Get gift wrap fee
     *
     * @paarm bool $store
     * @return float|int|mixed
     */
    public function getGiftWrapFee($store = false)
    {
        if ($store && $this->getSource()->getGiftwrapFee()) {
            return $this->getSource()->getGiftwrapFee();
        } elseif (!$store && $this->getSource()->getBaseGiftwrapFee()) {
            return $this->getSource()->getBaseGiftwrapFee();
        }
        $order = $this->getModel()->getOrder();
        $invoiceData = $this->request->getParam('creditmemo', []);
        $invoiceItems = $invoiceData['items'] ?? [];

        return $this->giftWrapData->getFinalRefundFee($order, $invoiceItems, $store);
    }
}
