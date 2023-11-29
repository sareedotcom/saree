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

namespace Pits\GiftWrap\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Pits\GiftWrap\Model\Wrap;
use Pits\GiftWrap\Model\GiftWrapData;

/**
 * Class MultishippingGiftOptions
 */
class MultishippingGiftOptions extends Template
{
    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;
    /**
     * @var Wrap
     */
    private $wrap;
    /**
     * @var Multishipping
     */
    private $_multishipping;

    /**
     * GiftOptions constructor.
     *
     * @param Template\Context $context
     * @param Wrap $wrap
     * @param Multishipping $multishipping
     * @param GiftWrapData $giftWrapData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Wrap $wrap,
        Multishipping $multishipping,
        GiftWrapData $giftWrapData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wrap = $wrap;
        $this->_multishipping = $multishipping;
        $this->giftWrapData = $giftWrapData;
    }

    public function getQuote()
    {
        return $this->_multishipping->getQuote();
    }

    /**
     * Get Order gift wrap message
     *
     * @return string|null
     */
    public function getGiftMessage($item = null)
    {
        try {
            $giftWrap = $this->wrap->getQuoteItemGiftWrapData($this->getQuote(), $item);
            return $giftWrap->getMessage();
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Check current order has a gift wrap for whole order
     *
     * @param $item
     * @return bool
     */
    public function isGiftWrap($item = null): bool
    {
        try {
            if($giftData = $this->wrap->getQuoteItemGiftWrapData($this->getQuote(), $item)){
                return (bool)$giftData->getId();
            }

        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return false;
    }

    /**
     * Get gift wrap image path
     *
     * @return string
     */
    public function getGiftWrapImagePath(): string
    {
        return $this->giftWrapData->getGiftWrapImagePath();
    }

    /**
     * Check single product or not
     *
     * @param $item
     * @return int|void
     */
    public function checkSingleProduct($item)
    {
        if($item)
        {
            $quote = $this->getQuote();
            foreach ($quote->getAllShippingAddresses() as $address) {
                foreach ($address->getAllVisibleItems() as $value) {
                    if($value->getQuoteItemId() == $item)
                    {
                        return count($address->getAllVisibleItems());
                    }
                }
            }
        }

        return 0;
    }
}