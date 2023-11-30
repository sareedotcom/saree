<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\ResourceModel\Quote\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as OriginalQuoteItemCollection;
use MageWorx\OrderEditor\Model\Quote\Item as OrderEditorQuoteItem;
use MageWorx\OrderEditor\Model\ResourceModel\Quote\Item as OrderEditorQuoteItemResource;

/**
 * Class Collection
 */
class Collection extends OriginalQuoteItemCollection
{
    /**
     * @var bool
     */
    private $extendedRecollectQuote;

    /**
     * Model initialization.
     * Change classes to own.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(OrderEditorQuoteItem::class, OrderEditorQuoteItemResource::class);
    }

    /**
     * Set Quote object to Collection.
     *
     * @important Method must be rewritten with that return type because of error in the interceptor
     * (return :self)
     *
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote): OriginalQuoteItemCollection
    {
        return parent::setQuote($quote);
    }

    /**
     * Reset the collection and inner join it to quotes table.
     *
     * Optionally can select items with specified product id only
     *
     * @important Method must be rewritten with that return type because of error in the interceptor
     * (return :self)
     *
     * @param string $quotesTableName
     * @param int $productId
     * @return $this
     */
    public function resetJoinQuotes($quotesTableName, $productId = null): OriginalQuoteItemCollection
    {
        return parent::resetJoinQuotes($quotesTableName, $productId);
    }

    /**
     * Add products to items and item options.
     *
     * @return OriginalQuoteItemCollection|$this
     */
    protected function _assignProducts(): OriginalQuoteItemCollection
    {
        \Magento\Framework\Profiler::start('QUOTE:' . __METHOD__, ['group' => 'QUOTE', 'method' => __METHOD__]);
        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addIdFilter(
            $this->_productIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        );
        $productCollection->setFlag('has_stock_status_filter', true);
        $productCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();

        $this->_eventManager->dispatch(
            'prepare_catalog_product_collection_prices',
            ['collection' => $productCollection, 'store_id' => $this->getStoreId()]
        );
        $this->_eventManager->dispatch(
            'sales_quote_item_collection_products_after_load',
            ['collection' => $productCollection]
        );

        foreach ($this as $item) {
            /** @var ProductInterface $product */
            $product = $productCollection->getItemById($item->getProductId());
            $qtyOptions = [];
            if ($product) {
                $product->setCustomOptions([]);
                $optionProductIds = $this->extendedGetOptionProductIds($item, $product, $productCollection);
                foreach ($optionProductIds as $optionProductId) {
                    $qtyOption = $item->getOptionByCode('product_qty_' . $optionProductId);
                    if ($qtyOption) {
                        $qtyOptions[$optionProductId] = $qtyOption;
                    }
                }
            } else {
                $item->isDeleted(true);
                $this->extendedRecollectQuote = true;
            }
            if (!$item->isDeleted()) {
                $item->setQtyOptions($qtyOptions)->setProduct($product);
                $item->checkData();
            }
        }
        if ($this->extendedRecollectQuote && $this->_quote) {
            $this->_quote->collectTotals();
        }
        \Magento\Framework\Profiler::stop('QUOTE:' . __METHOD__);

        return $this;
    }

    /**
     * Get product Ids from option.
     *
     * @param QuoteItem $item
     * @param ProductInterface $product
     * @param ProductCollection $productCollection
     * @return array
     */
    private function extendedGetOptionProductIds(
        QuoteItem $item,
        ProductInterface $product,
        ProductCollection $productCollection
    ): array {
        $optionProductIds = [];
        foreach ($item->getOptions() as $option) {
            /**
             * Call type-specific logic for product associated with quote item
             */
            $product->getTypeInstance()->assignProductToOption(
                $productCollection->getItemById($option->getProductId()),
                $option,
                $product
            );

            if (is_object($option->getProduct()) && $option->getProduct()->getId() != $product->getId()) {
                $optionProductIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
            }
        }

        return $optionProductIds;
    }
}
