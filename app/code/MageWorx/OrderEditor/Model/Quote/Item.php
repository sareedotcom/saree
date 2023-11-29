<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Quote;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection as QuoteItemOptionCollection;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory as QuoteItemOptionCollectionFactory;

/**
 * Class Item
 */
class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * @var QuoteItemOptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * Item constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param QuoteItemOptionCollectionFactory $optionCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        QuoteItemOptionCollectionFactory $optionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * @var string
     */
    const PREFIX_ID = 'q';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\MageWorx\OrderEditor\Model\ResourceModel\Quote\Item::class);
    }

    /**
     * Get child items
     *
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem[]
     */
    public function getChildren()
    {
        $children = $this->_children;

        if ($this->getData('product_type') == Configurable::TYPE_CODE && empty($children)) {
            if (!$this->getParentItem()) {
                throw new LocalizedException(__('Configurable item %1 has no children', $this->getId()));
            } else {
                return $this->getParentItem()->getChildren();
            }
        }

        return $children;
    }

    /**
     * Get item product type
     *
     * @return string
     */
    public function getProductType()
    {
        $type = $this->_getData(self::KEY_PRODUCT_TYPE);

        if (!$type) {
            $type = parent::getProductType();
        }

        return $type;
    }

    /**
     * Add all options
     *
     * @return $this
     * @throws LocalizedException
     */
    public function loadOptions()
    {
        if (!$this->getItemId()) {
            throw new LocalizedException(__('Quote item must have ID before loading options'));
        }

        /** @var QuoteItemOptionCollection $optionsCollection */
        $optionsCollection = $this->optionCollectionFactory->create();
        $options           = $optionsCollection->addItemFilter([$this->getItemId()])
                                               ->getOptionsByItem($this);
        $this->setOptions($options);

        return $this;
    }
}
