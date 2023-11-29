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

namespace Pits\GiftWrap\Observer;

use Exception;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Quote\Model\QuoteRepository;
use Pits\GiftWrap\Api\Data\WrapInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Pits\GiftWrap\Model\Wrap;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveProduct
 */
class RemoveProduct implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var Session
     */
    private $_checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $_logger;

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
     * @var Cart
     */
    private $cartHelper;

    /**
     * RemoveProduct constructor.
     *
     * @param JsonSerializer $jsonSerializer
     * @param RequestInterface $request
     * @param Session $checkoutSession
     * @param WrapRepositoryInterface $wrapRepository
     * @param Wrap $wrap
     * @param QuoteRepository $QuoteRepository
     * @param LoggerInterface $logger
     * @param Cart $cartHelper
     * @return void
     */
    public function __construct(
        JsonSerializer $jsonSerializer,
        RequestInterface $request,
        Session $checkoutSession,
        WrapRepositoryInterface $wrapRepository,
        Wrap $wrap,
        QuoteRepository $QuoteRepository,
        LoggerInterface $logger,
        Cart $cartHelper
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
        $this->wrap = $wrap;
        $this->wrapRepository = $wrapRepository;
        $this->QuoteRepository = $QuoteRepository;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Remove Giftwrap from removed product.
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {

        $quoteItem = $observer->getQuoteItem();
        $quote = $quoteItem->getQuote();
        try {
            $itemId = $quoteItem->getId();
            if ($giftwrapData = $this->wrap->getQuoteItemGiftWrapData($quote, $itemId)) {
                $quoteGiftWrap = $this->wrapRepository->getById($giftwrapData->getId());
                $this->wrapRepository->delete($quoteGiftWrap);
                $this->updateQuoteGiftWrap($itemId);
            }

            if (Count($quote->getAllVisibleItems()) == 1) {
                foreach ($quote->getAllVisibleItems() as $item) {
                    $itemId = $item->getId();
                    if ($this->wrap->getQuoteItemGiftWrapData($quote, $itemId)) {
                        $this->updateQuoteGiftWrap($itemId, true);
                    }
                }

            }
            if (Count($quote->getAllVisibleItems()) == 0) {
                $this->updateQuoteGiftWrap(0, true);
            }
        } catch (NoSuchEntityException $exception) {
            $this->_logger->critical(__('ERROR: No Gift wrap found. %1', $exception->getMessage()));
        } catch (Exception $exception) {
            $this->_logger->critical(__('ERROR: Gift wrap delete failed. %1', $exception->getMessage()));
        }
    }

    /**
     * Update quote gift wrap
     *
     * @param bool $quoteItemId
     * @param bool $singleProduct
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateQuoteGiftWrap($quoteItemId = false, $singleProduct = false)
    {
        $quote = $this->_checkoutSession->getQuote();
        $quoteGiftWrapData = [];
        if ($quote->getGiftWrapData()) {
            $quoteGiftWrapData = $this->jsonSerializer->unserialize($quote->getGiftWrapData());
        }
        if (!empty($quoteGiftWrapData)) {
            if ($quoteItemId && !$singleProduct) {
                unset($quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER][$quoteItemId]);
            } else {
                $quoteGiftWrapData[WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER] = null;
                unset($quoteGiftWrapData[WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER][$quoteItemId]);
            }
        }
        $quote->setGiftWrapData($this->jsonSerializer->serialize($quoteGiftWrapData));
        $this->QuoteRepository->save($quote);
    }
}
