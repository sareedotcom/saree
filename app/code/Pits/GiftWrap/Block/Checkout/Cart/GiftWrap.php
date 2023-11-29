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

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;
use Pits\GiftWrap\Model\Wrap as GiftWrapModel;
use Psr\Log\LoggerInterface;

/**
 * Class GiftWrap
 */
class GiftWrap extends Template
{
    /**
     * @var GiftWrapModel
     */
    private $giftWrap;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var GiftWrapHelper
     */
    private $giftWrapHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GiftWrap constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param GiftWrapHelper $giftWrapHelper
     * @param GiftWrapModel $giftWrap
     * @param LoggerInterface $logger
     * @param Json $json
     * @param Context $context
     * @param array $data
     * @return void
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        GiftWrapHelper $giftWrapHelper,
        GiftWrapModel $giftWrap,
        LoggerInterface $logger,
        Json $json,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->giftWrap = $giftWrap;
        $this->json = $json;
        $this->giftWrapHelper = $giftWrapHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * Get gift wrap price
     *
     * @return float|int
     */
    public function getGiftWrapPrice()
    {
        return $this->giftWrapHelper->getGiftWrapUnitPrice();
    }

    /**
     * Get gift wrap title
     *
     * @return string
     */
    public function getGiftWrapTitle()
    {
        return $this->giftWrapHelper->getGiftWrapTitle();
    }

    /**
     * Get gift wrap is show in summary
     *
     * @return string
     */
    public function isShowSummary()
    {
        return $this->giftWrapHelper->isShowSummary();
    }

    /**
     * Get gift wrap fee label
     *
     * @return string
     */
    public function getGiftWrapFeeLabel()
    {
        return $this->giftWrapHelper->getGiftWrapFeeLabel();
    }

    /**
     * Get gift wrap item note
     *
     * @return string
     */
    public function getItemWrapNote()
    {
        return $this->giftWrapHelper->getItemWrapNote();
    }

    /**
     * Get cart wrap title
     *
     * @return string
     */
    public function getCartWrapTitle()
    {
        return $this->giftWrapHelper->getCartWrapTitle();
    }

    /**
     * Get cart wrap note
     *
     * @return string
     */
    public function getCartWrapNote()
    {
        return $this->giftWrapHelper->getCartWrapNote();
    }

    /**
     * Get quote item gift wrap data
     *
     * @return bool|false|string
     */
    public function getQuoteItemGiftWrapData()
    {
        return $this->getGiftWrapData(true);
    }

    /**
     * Get Quote gift wrap data
     *
     * @return bool|false|string
     */
    public function getQuoteGiftWrapData()
    {
        return $this->getGiftWrapData();
    }

    /**
     * Get quote or quote item gift wrap data
     *
     * @param bool $isItemData
     * @return bool|false|string
     */
    public function getGiftWrapData($isItemData = false)
    {
        $quoteItemId = null;
        $giftWrapData = [];
        try {
            if ($isItemData) {
                $quoteItemId = $this->getItem()->getId();
            }

            $quoteGiftWrapData =
                $this->giftWrap->getQuoteItemGiftWrapData($this->checkoutSession->getQuote(), $quoteItemId);
            if ($quoteGiftWrapData) {
                $giftWrapData = $quoteGiftWrapData->getData();
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $this->json->serialize($giftWrapData);
    }

    /**
     * Get can cart wrap apply
     *
     * @return bool|false|string
     */
    public function canCartWrap() : bool
    {
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() != 'virtual' && $item->getProductType() != 'downloadable') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get can item wrap apply
     *
     * @param mixed $item
     * @return bool|false|string
     */
    public function canItemWrap($item) : bool
    {
        $type = $item->getProductType();
        return ($type != 'virtual' && $type != 'downloadable');
    }

    /**
     * Get cart has morethan 1 simple product
     *
     * @return bool|false|string
     */
    public function hasTwoMoreInCart() : bool
    {
        $itemCount = 0;
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() != 'virtual' && $item->getProductType() != 'downloadable') {
                $itemCount++;
            }
        }
        return ($itemCount > 1);
    }
}
