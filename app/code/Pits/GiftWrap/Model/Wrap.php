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

namespace Pits\GiftWrap\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Pits\GiftWrap\Api\Data\WrapExtensionInterface;
use Pits\GiftWrap\Api\Data\WrapInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;
use Pits\GiftWrap\Model\ResourceModel\Wrap as WrapResource;

/**
 * Class Wrap
 *
 * @package Pits\GiftWrap\Model
 */
class Wrap extends AbstractExtensibleModel implements WrapInterface, IdentityInterface
{
    /**
     * Cache Tag constant
     */
    const CACHE_TAG = 'gift_wrap';

    /**
     * Unique identifier for use within cache storage.
     *
     * @var string
     */
    protected $_cacheTag = 'gift_wrap';

    /**
     * Unique prefix used when executing events.
     *
     * @var string
     */
    protected $_eventPrefix = 'gift_wrap';

    /**
     * Gift wrap fee identifier
     */
    const GIFT_WRAP_FEE_IDENTIFIER = 'giftwrap_fee';

    /**
     * Base gift wrap fee identifier
     */
    const BASE_GIFT_WRAP_FEE_IDENTIFIER = 'base_giftwrap_fee';

    /**
     * Canceled gift wrap fee identifier
     */
    const GIFT_WRAP_FEE_CANCELED_IDENTIFIER = 'gift_wrap_canceled';

    /**
     * Invoiced gift wrap fee identifier
     */
    const GIFT_WRAP_FEE_INVOICED_IDENTIFIER = 'gift_wrap_invoiced';

    /**
     * Base invoiced gift wrap fee identifier
     */
    const BASE_GIFT_WRAP_FEE_INVOICED_IDENTIFIER = 'base_gift_wrap_invoiced';

    /**
     * Base invoiced gift wrap fee identifier
     */
    const GIFT_WRAP_FEE_REFUNDED_IDENTIFIER = 'gift_wrap_refunded';

    /**
     * Base invoiced gift wrap fee identifier
     */
    const BASE_GIFT_WRAP_FEE_REFUNDED_IDENTIFIER = 'base_gift_wrap_refunded';

    /**
     * Quote gift wrap data field
     */
    const GIFT_WRAP_FIELD_NAME = 'gift_wrap_data';

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * Wrap constructor.
     *
     * @param WrapRepositoryInterface $wrapRepository
     * @param Json $json
     * @param Data $priceHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        WrapRepositoryInterface $wrapRepository,
        Json $json,
        Data $priceHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->json = $json;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->wrapRepository = $wrapRepository;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Wrap construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(WrapResource::class);
    }

    /**
     * Get model identities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get message
     *
     * @return mixed|string|null
     */
    public function getMessage()
    {
        return $this->_getData(self::GIFT_WRAP_MESSAGE_COLUMN);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Wrap
     */
    public function setMessage($message)
    {
        return $this->setData(self::GIFT_WRAP_MESSAGE_COLUMN, $message);
    }

    /**
     * Get price
     *
     * @return int|mixed|null
     */
    public function getPrice()
    {
        return $this->_getData(self::GIFT_WRAP_PRICE_COLUMN);
    }

    /**
     * Set price
     *
     * @param int $price
     * @return Wrap
     */
    public function setPrice($price)
    {
        return $this->setData(self::GIFT_WRAP_PRICE_COLUMN, $price);
    }

    /**
     * Get is whole cart wrap
     *
     * @return int|mixed|null
     */
    public function getIsWholeOrder()
    {
        return $this->_getData(self::GIFT_WRAP_BELONGS_TO_WHOLE_ORDER_COLUMN);
    }

    /**
     * Set is whole order wrap data
     *
     * @param int $isWholeOrder
     * @return Wrap
     */
    public function setIsWholeOrder($isWholeOrder)
    {
        return $this->setData(self::GIFT_WRAP_BELONGS_TO_WHOLE_ORDER_COLUMN, $isWholeOrder);
    }

    /**
     * Get extension attributes
     *
     * @return ExtensionAttributesInterface|WrapExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set extension attributes
     *
     * @param WrapExtensionInterface $extensionAttributes
     * @return Wrap|void
     */
    public function setExtensionAttributes(WrapExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get quote item gift wrap data
     *
     * @param Quote $quote
     * @param int $quoteItemId
     * @return WrapInterface|void
     */
    public function getQuoteItemGiftWrapData($quote, $quoteItemId = null)
    {
        try {
            $giftWrapData = [];
            if ($quoteWrapData = $quote->getData(self::GIFT_WRAP_FIELD_NAME)) {
                $giftWrapData = $this->json->unserialize($quoteWrapData);
            }
            if (!empty($giftWrapData)) {
                $giftWrapId = null;
                if (isset($giftWrapData['items'][$quoteItemId])
                    && $giftWrapData['items'][$quoteItemId]) {
                    $giftWrapId = $giftWrapData['items'][$quoteItemId];
                }
                if (!$quoteItemId && isset($giftWrapData['whole_cart'])) {
                    $giftWrapId = $giftWrapData['whole_cart'];
                }
                if ($giftWrapId) {
                    return $this->wrapRepository->getById($giftWrapId);
                }
            }
        } catch (NoSuchEntityException | LocalizedException $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return;
    }

    /**
     * Get all gift wraps associated with quote or order
     *
     * @param Quote|Order $instance
     * @return mixed|null
     */
    public function getAllAssociatedGiftWraps($instance)
    {
        $giftWrapData = [];
        $giftWrapCollection = null;
        if ($quoteWrapData = $instance->getData(self::GIFT_WRAP_FIELD_NAME)) {
            $giftWrapData = $this->json->unserialize($quoteWrapData);
        }
        if (!empty($giftWrapData)) {
            $giftWrapCollection = $this->getGiftWrapCollection($giftWrapData);
        }

        return $giftWrapCollection;
    }

    /**
     * Get gift wrap collection data associated with quote or order wrap
     *
     * @param array $giftWrapData
     * @return mixed
     */
    public function getGiftWrapCollection($giftWrapData)
    {
        $assignedWrapIds = [];
        foreach ($giftWrapData as $type => $value) {
            if ($type == WrapInterface::QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER) {
                $assignedWrapIds[] = $value;
            } elseif ($type == WrapInterface::QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER) {
                foreach ($value as $wrapId) {
                    $assignedWrapIds[] = $wrapId;
                }
            }
        }
        $assignedWrapIds = array_filter($assignedWrapIds);
        $this->searchCriteriaBuilder->addFilter(WrapInterface::GIFT_WRAP_ID_COLUMN, $assignedWrapIds, 'in');

        return $this->wrapRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * Set Store currency Fee to Gift wrap data
     *
     * @param string $quoteWrapData
     * @return void
     */
    public function setStoreGiftWrapPrice(string $quoteWrapData)
    {
        $giftWrapData = $this->json->unserialize($quoteWrapData);
        if (!empty($giftWrapData) && isset($giftWrapData['items'])) {
            foreach ($giftWrapData['items'] as $itemGiftWrapId) {
                if ($itemGiftWrapId) {
                    $this->saveStoreGiftWrapPrice($itemGiftWrapId);
                }
            }
        }
        $giftWrapId = $giftWrapData['whole_cart'] ?? null;
        if ($giftWrapId) {
            $this->saveStoreGiftWrapPrice($giftWrapId);
        }
    }

    /**
     * Save gift store price to DB
     *
     * @param int $itemGiftWrapId
     * @return void
     */
    public function saveStoreGiftWrapPrice(int $itemGiftWrapId)
    {
        try {
            $quoteGiftWrapData = $this->wrapRepository->getById($itemGiftWrapId);
            $quoteGiftWrapData->setStorePrice($this->getStorePriceConversion($quoteGiftWrapData->getPrice()));
            $this->wrapRepository->save($quoteGiftWrapData);
        } catch (NoSuchEntityException | LocalizedException $exception) {
            $this->_logger->error($exception->getMessage());
        }
    }

    /**
     * Get Store front currency
     *
     * @param int|float $amount
     * @return float|string
     */
    public function getStorePriceConversion($amount)
    {
        return $this->priceHelper->currency($amount, false, false);
    }

    /**
     *  Add attribute data to object
     *
     * @param OrderRepositoryInterface|CartRepositoryInterface $object
     * @param CartExtensionFactory|OrderExtensionFactory $extensionFactoryObject
     * @return CartRepositoryInterface|OrderRepositoryInterface
     */
    public function addGiftExtensionAttribute($object, $extensionFactoryObject)
    {
        $info = $object->getData(Wrap::GIFT_WRAP_FIELD_NAME);
        $extensionAttributes = $object->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $extensionFactoryObject;
        $extensionAttributes->setGiftWrap($info);
        $object->setExtensionAttributes($extensionAttributes);

        return $object;
    }

    /**
     * Add attribute data to object items
     *
     * @param CartSearchResultsInterface|OrderSearchResultInterface $searchResult
     * @param CartExtensionFactory|OrderExtensionFactory $extensionFactoryObject
     * @return CartSearchResultsInterface|OrderSearchResultInterface
     */
    public function addGiftExtensionAttributeForItem($searchResult, $extensionFactoryObject)
    {
        $items = $searchResult->getItems();
        foreach ($items as &$item) {
            $info = $item->getData(Wrap::GIFT_WRAP_FIELD_NAME);
            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $extensionFactoryObject;
            $extensionAttributes->setGiftWrap($info);
            $item->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
