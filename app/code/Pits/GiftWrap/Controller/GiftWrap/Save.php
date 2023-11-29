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

namespace Pits\GiftWrap\Controller\GiftWrap;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Quote\Model\QuoteRepository;
use Pits\GiftWrap\Api\Data\WrapInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Pits\GiftWrap\Controller\AbstractAjaxAction;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;
use Pits\GiftWrap\Model\Wrap;
use Pits\GiftWrap\Model\WrapFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Save
 *
 * @package Pits\GiftWrap\Controller\GiftWrap
 */
class Save extends AbstractAjaxAction
{
    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var WrapFactory
     */
    private $wrapFactory;

    /**
     * @var GiftWrapHelper
     */
    private $giftWrapHelper;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * Save constructor.
     *
     * @param Wrap $wrap
     * @param WrapRepositoryInterface $wrapRepository
     * @param WrapFactory $wrapFactory
     * @param GiftWrapHelper $giftWrapHelper
     * @param JsonSerializer $jsonSerializer
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param QuoteRepository $quoteRepository
     * @param Context $context
     * @return void
     */
    public function __construct(
        Wrap $wrap,
        WrapRepositoryInterface $wrapRepository,
        WrapFactory $wrapFactory,
        GiftWrapHelper $giftWrapHelper,
        JsonSerializer $jsonSerializer,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        QuoteRepository $quoteRepository,
        Context $context
    ) {
        parent::__construct(
            $jsonSerializer,
            $checkoutSession,
            $logger,
            $resultJsonFactory,
            $context
        );
        $this->wrap = $wrap;
        $this->wrapRepository = $wrapRepository;
        $this->wrapFactory = $wrapFactory;
        $this->giftWrapHelper = $giftWrapHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Prepare response based on the delete functionality
     *
     * @return array|bool[]|mixed
     */
    public function prepareResponse()
    {
        try {
            $response = ['error' => true];
            $post = $this->jsonSerializer->unserialize($this->getRequest()->getContent());
            $isDeleteRequest = isset($post['removeGiftWrap']) && $post['removeGiftWrap'];
            $quoteItemId = $post['itemId'];
            $quote = $this->checkoutSession->getQuote();
            $quoteGiftWrapData = $this->wrap->getQuoteItemGiftWrapData($quote, $quoteItemId);
            if ($quoteGiftWrapData && $quoteGiftWrapData->getId()) {
                if ($isDeleteRequest) {
                    $this->wrapRepository->delete($quoteGiftWrapData);
                    $quoteGiftWrapData = null;
                } else {
                    $quoteGiftWrapData->setMessage($post['message']);
                    $quoteGiftWrapData = $this->wrapRepository->save($quoteGiftWrapData);
                }
            } else {
                // Insert new quote item gift wrap data
                $quoteGiftWrapData = $this->wrapFactory->create();
                $quoteGiftWrapData->setMessage($post['message']);
                $quoteGiftWrapData->setPrice($this->giftWrapHelper->getGiftWrapUnitPrice());
                $quoteGiftWrapData->setStorePrice($this->wrap->getStorePriceConversion($this->giftWrapHelper->getGiftWrapUnitPrice()));
                $quoteGiftWrapData->setIsWholeOrder($post['is_whole_order']);
                $quoteGiftWrapData = $this->wrapRepository->save($quoteGiftWrapData);
            }
            $this->updateQuoteGiftWrap($quoteGiftWrapData, $quoteItemId);
            $response['error'] = false;
            $response['giftWrap'] = $quoteGiftWrapData ? $quoteGiftWrapData->getData() : null;
        } catch (Exception $e) {
            $response = ['message' => __($e->getMessage())];
        }

        return $response;
    }

    /**
     * Update quote gift wrap
     *
     * @param Wrap $giftWrap
     * @param false $quoteItemId
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function updateQuoteGiftWrap($giftWrap, $quoteItemId = false)
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteGiftWrapData = [];
        if ($quote->getGiftWrapData()) {
            $quoteGiftWrapData = $this->jsonSerializer->unserialize($quote->getGiftWrapData());
        }
        if (empty($quoteGiftWrapData)) {
            if (!$quoteItemId) {
                // Whole cart wrap
                $quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER] = [];
                $quoteGiftWrapData[WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER] =
                    $giftWrap ? $giftWrap->getId() : null;
            } else {
                $quoteGiftWrapData[WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER] = null;
                $quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER][$quoteItemId] =
                    $giftWrap->getId();
            }
        } else {
            if (!$quoteItemId) {
                // Update data for whole cart
                $quoteGiftWrapData[WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER] =
                    $giftWrap ? $giftWrap->getId() : null;
            } else {
                $quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER][$quoteItemId] =
                    $giftWrap ? $giftWrap->getId() : '';
            }
        }
        $quote->setGiftWrapData($this->jsonSerializer->serialize($quoteGiftWrapData));
        $this->quoteRepository->save($quote);
    }
}
