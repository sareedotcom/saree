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
 * @package  Pits_Giftwrap
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
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Pits\GiftWrap\Controller\AbstractAjaxAction;
use Pits\GiftWrap\Model\Wrap;
use Psr\Log\LoggerInterface;
use Pits\GiftWrap\Api\Data\WrapInterface;

/**
 * Class Delete
 *
 * @package Pits\GiftWrap\Controller\GiftWrap
 */
class Delete extends AbstractAjaxAction
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
     * @var QuoteRepository
     */
    private $QuoteRepository;

    /**
     * Delete constructor.
     *
     * @param WrapRepositoryInterface $wrapRepository
     * @param Wrap $wrap
     * @param JsonSerializer $jsonSerializer
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     * @param QuoteRepository $QuoteRepository
     * @param Context $context
     * @return void
     */
    public function __construct(
        WrapRepositoryInterface $wrapRepository,
        Wrap $wrap,
        JsonSerializer $jsonSerializer,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory,
        QuoteRepository $QuoteRepository,
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
        $this->QuoteRepository = $QuoteRepository;
    }

    /**
     * Prepare gift wrap delete response
     *
     * @return bool[]|mixed
     */
    public function prepareResponse()
    {
        try {
            $post = $this->jsonSerializer->unserialize($this->getRequest()->getContent());
            $itemId = $post['itemId'] ?? null;
            if ($this->isValidWrapId($post['wrapId'], $itemId)) {
                $quoteGiftWrap = $this->wrapRepository->getById($post['wrapId']);
                $this->wrapRepository->delete($quoteGiftWrap);
                $response['error'] = false;
                $this->updateQuoteGiftWrap($itemId);
            } else {
                $response['message'] = __('Gift wrap update failed.');
            }
        } catch (NoSuchEntityException $exception) {
            $response['message'] = __('No Gift wrap found.');
        } catch (Exception $exception) {
            $response['message'] = __('Something went wrong while removing gift wrap.');
            $this->logger->critical(__('ERROR: Gift wrap delete failed. %1', $exception->getMessage()));
        }

        return $response;
    }

    /**
     * Check if wrap id provided is associated with the current quote or cart item
     *
     * @param int $wrapId
     * @param int|null $itemId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isValidWrapId($wrapId, $itemId)
    {
        $quoteGiftWrapData = $this->wrap->getQuoteItemGiftWrapData($this->checkoutSession->getQuote(), $itemId);
        if (!$quoteGiftWrapData) {
            return false;
        }

        return $quoteGiftWrapData->getId() == $wrapId;
    }

    /**
     * Update quote gift wrap
     *
     * @param bool $quoteItemId
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateQuoteGiftWrap($quoteItemId = false)
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteGiftWrapData = [];
        if ($quote->getGiftWrapData()) {
            $quoteGiftWrapData = $this->jsonSerializer->unserialize($quote->getGiftWrapData());
        }
        if (!empty($quoteGiftWrapData)) {
            if (!$quoteItemId) {
                // Update data for whole cart
                $quoteGiftWrapData[WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER] = null;
            } else {
                $quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER][$quoteItemId] = null;
            }
        }
        $quote->setGiftWrapData($this->jsonSerializer->serialize($quoteGiftWrapData));
        $this->QuoteRepository->save($quote);
    }
}
