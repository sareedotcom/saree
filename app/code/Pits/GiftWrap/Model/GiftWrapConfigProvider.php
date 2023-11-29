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

use \Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;

/**
 * Class GiftWrapConfigProvider
 *
 * @package Pits\GiftWrap\Model
 */
class GiftWrapConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var GiftWrapHelper
     */
    private $giftWrapHelper;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var UrlInterface
     */
    private $_urlInterface;

    /**
     * GiftWrapConfigProvider constructor.
     *
     * @param Json $jsonSerializer
     * @param WrapRepositoryInterface $wrapRepository
     * @param CheckoutSession $checkoutSession
     * @param Data $priceHelper
     * @param GiftWrapHelper $giftWrapHelper
     * @param UrlInterface $urlInterface
     * @return void
     */
    public function __construct(
        Json $jsonSerializer,
        WrapRepositoryInterface $wrapRepository,
        CheckoutSession $checkoutSession,
        Data $priceHelper,
        GiftWrapHelper $giftWrapHelper,
        UrlInterface $urlInterface
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->wrapRepository = $wrapRepository;
        $this->priceHelper = $priceHelper;
        $this->giftWrapHelper = $giftWrapHelper;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * Adding Gift wrap data to checkout.
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        $configArray = $giftWrap = $giftWrapItemArray = $giftWrapDataArray = [];
        $quote = $this->checkoutSession->getQuote();

        $giftWrap['order'] = null;
        $giftWrap['giftWrapSaveUrl'] = $this->_urlInterface->getUrl('giftWrap/giftWrap/save');
        $giftWrap['giftWrapRemoveUrl'] = $this->_urlInterface->getUrl('giftWrap/giftWrap/delete');
        $quoteGiftWrapPrice = $this->giftWrapHelper->getGiftWrapUnitPrice();
        $giftWrap['giftWrapLabelWithPrice'] =
            __('Gift Wrap %1', $this->priceHelper->currency($quoteGiftWrapPrice, true, false));

        if ($giftWrapData = $quote->getGiftWrapData()) {
            $giftWrapDataArray = $this->jsonSerializer->unserialize($giftWrapData);
        }
        if ($giftWrapDataArray && isset($giftWrapDataArray['whole_cart'])
            && $giftWrapOrderData = $this->wrapRepository->getById($giftWrapDataArray['whole_cart'])) {
            $giftWrap['order'] = $giftWrapOrderData->getData();
        }

        foreach ($quote->getAllItems() as $_item) {
            if ($_item->getParentItemId() == null) {
                $giftWrapItemData = ['id' => null, 'message' => null, 'itemId' => $_item->getId()];

                if ($giftWrapDataArray && isset($giftWrapDataArray['items'])
                    && isset($giftWrapDataArray['items'][$_item->getId()])
                    && $giftWrapDataArray['items'][$_item->getId()] != ""
                    &&
                    $giftWrapOrderData = $this->wrapRepository->getById($giftWrapDataArray['items'][$_item->getId()])) {
                    $giftWrapItemData = $giftWrapOrderData->getData();
                    $giftWrapItemData['itemId'] = $_item->getId();
                }
                $giftWrapItemArray[] = $giftWrapItemData;
            }
        }
        $giftWrap['items'] = $giftWrapItemArray;
        $configArray['giftWrapData'] = $this->jsonSerializer->serialize($giftWrap);

        return $configArray;
    }
}
