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

namespace Pits\GiftWrap\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Pits\GiftWrap\Model\ResourceModel\Wrap as WrapResource;


/**
 * Class PriceCalculator
 *
 * @package Pits\GiftWrap\Model
 */
class PriceCalculator extends AbstractModel
{
    /**
     * @var CartRepositoryInterface
     */
    protected $CartRepositoryInterface;

    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    
    /**
     * @var Json
     */
    private $json;

    /**
     * PriceCalculator constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param Wrap $wrap
     * @param Context $context
     * @param Registry $registry
     * @param CartRepositoryInterface $CartRepositoryInterface
     * @param QuoteFactory $quoteFactory
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Wrap $wrap,
        Context $context,
        Registry $registry,
        CartRepositoryInterface $CartRepositoryInterface,
        QuoteFactory $quoteFactory,
        Json $json,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->wrap = $wrap;
        $this->checkoutSession = $checkoutSession;
        $this->CartRepositoryInterface = $CartRepositoryInterface;
        $this->quoteFactory = $quoteFactory;
        $this->json = $json;
    }

    /**
     * Get quote gift wrap fee
     *
     * @return float
     */
    public function getQuoteGiftWrapFee()
    {
        $GiftWrapFee = 0;
        try {
            $quoteId = $this->checkoutSession->getQuoteId();
            if ($quoteId) {
                $quote = $this->CartRepositoryInterface->get($quoteId);
                $quoteGiftWraps = $this->wrap->getAllAssociatedGiftWraps($quote);
                $GiftWrapFee = $this->calculateGiftWrapFee($quoteGiftWraps);
            }
        } catch (NoSuchEntityException $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $GiftWrapFee;
    }

    /**
     * Get quote gift wrap fee
     *
     * @return float
     */
    public function getQuoteAddressGiftWrapFee($itemIds)
    {
        $GiftWrapFee = 0;
        try {
            $quoteId = $this->checkoutSession->getQuoteId();
            if ($quoteId) {
                $quote = $this->CartRepositoryInterface->get($quoteId);
                $quoteGiftWraps = $this->wrap->getAllAssociatedGiftWraps($quote);
                $GiftWrapFee = $this->calculateGiftWrapFeeForAddress($quoteGiftWraps, false, $itemIds );
            }
        } catch (NoSuchEntityException $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $GiftWrapFee;
    }

    /**
     * Get order gift wrap fee
     *
     * @param Order $order
     * @param bool $store
     * @return float
     */
    public function getOrderGiftWrapFee($order, $store = false)
    {
        $orderGiftWraps = $this->wrap->getAllAssociatedGiftWraps($order);

        return $this->calculateGiftWrapFee($orderGiftWraps, $store);
    }

    /**
     * Get order gift wrap fee
     *
     * @param Order $order
     * @param bool $store
     * @return float
     */
    public function getOrderGiftWrapFeeFromQuote($order, $store = false)
    {
        $orderGiftWraps = $this->getAllGiftWraps($order);

        return $this->calculateGiftWrapFee($orderGiftWraps, $store);
    }

    /**
     * Get all gift wraps associated with quote or order
     *
     * @param $order
     * @return mixed|null
     */
    public function getAllGiftWraps($order)
    {
        $giftWrapCollection = null;
        $quote = $this->quoteFactory->create()->load($order->getQuoteId());
        if($quote->getIsMultiShipping())
        {
            $giftWrapData = $this->json->unserialize($quote->getGiftWrapData());
            $orderGiftwrapData = array();
            if($giftWrapData['whole_cart'])
            {
                $orderGiftwrapData['whole_cart'] = $giftWrapData['whole_cart'];
            }
            $ordeGiftwrapItemData = array();
	    if($order->getTotalItemCount() > 1) {
            foreach ($order->getAllVisibleItems() as $item) {
                foreach ($giftWrapData['items'] as $giftWrapItemid => $giftWrapItem) {
                    if($giftWrapItemid == $item->getQuoteItemId())
                    {
                        $ordeGiftwrapItemData[$giftWrapItemid] = $giftWrapItem;
                    }
                }
            }
	    }
            $orderGiftwrapData['items'] = $ordeGiftwrapItemData;
            if (!empty($orderGiftwrapData)) {
                $giftWrapCollection = $this->wrap->getGiftWrapCollection($orderGiftwrapData);
            }
        }
        else{
            $giftWrapCollection = $this->wrap->getAllAssociatedGiftWraps($order);
        }

        return $giftWrapCollection;

    }

    /**
     * Fees will be the sum of all gift wraps selected
     *
     * @param WrapResource $giftWraps
     * @param bool $store
     * @return float
     */
    public function calculateGiftWrapFee($giftWraps, $store = false)
    {
        $price = 0.00;
        if ($giftWraps && $giftWraps->getTotalCount()) {
            foreach ($giftWraps->getItems() as $giftWrap) {
                if ($store) {
                    $price += $giftWrap->getStorePrice();
                } else {
                    $price += $giftWrap->getPrice();
                }
            }
        }

        return $price;
    }

    /**
     * Fees will be the sum of all gift wraps selected
     *
     * @param WrapResource $giftWraps
     * @param bool $store
     * @return float
     */
    public function calculateGiftWrapFeeForAddress($giftWraps, $store = false, $itemIds = null)
    {
        $price = 0.00;
        if ($giftWraps && $giftWraps->getTotalCount()) {
            foreach ($giftWraps->getItems() as $giftWrap) {
                if(in_array($giftWrap->getId(), $itemIds))
                {
                    if ($store) {
                        $price += $giftWrap->getStorePrice();
                    } else {
                        $price += $giftWrap->getPrice();
                    }
                }
            }
        }

        return $price;
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
