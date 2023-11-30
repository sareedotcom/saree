<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\Edit;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\Quote\Item as OriginalQuoteItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as QuoteItemCollectionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OriginalOrderItem;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use MageWorx\OrderEditor\Api\OrderItemRepositoryInterface as OrderEditorOrderItemRepository;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface as OrderEditorOrderRepository;
use MageWorx\OrderEditor\Api\QuoteItemRepositoryInterface as OrderEditorQuoteItemRepository;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Helper\Data as Helper;
use MageWorx\OrderEditor\Model\Order as OrderEditorOrderModel;
use MageWorx\OrderEditor\Model\Quote as OrderEditorQuoteModel;
use MageWorx\OrderEditor\Model\Quote\Item as OrderEditorQuoteItem;

/**
 * Class Quote
 */
class Quote
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var OrderEditorQuoteModel
     */
    protected $quote;

    /**
     * @var ToOrderItem
     */
    protected $quoteItemToOrderItem;

    /**
     * @var QuoteItemCollectionFactory
     */
    protected $quoteItemCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var OrderEditorQuoteItemRepository
     */
    protected $oeQuoteItemRepository;

    /**
     * @var OrderEditorOrderItemRepository
     */
    protected $oeOrderItemRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    protected $searchCriteriaBuilderFactory;

    /**
     * @var OrderEditorOrderRepository
     */
    protected $oeOrderRepository;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Quote constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ToOrderItem $quoteItemToOrderItem
     * @param QuoteItemCollectionFactory $quoteItemCollectionFactory
     * @param OrderEditorQuoteItemRepository $oeQuoteItemRepository
     * @param OrderEditorOrderItemRepository $oeOrderItemRepository
     * @param OrderEditorOrderRepository $oeOrderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param MessageManagerInterface $messageManager
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param Helper $helper
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        QuoteRepositoryInterface $quoteRepository,
        ToOrderItem $quoteItemToOrderItem,
        QuoteItemCollectionFactory $quoteItemCollectionFactory,
        OrderEditorQuoteItemRepository $oeQuoteItemRepository,
        OrderEditorOrderItemRepository $oeOrderItemRepository,
        OrderEditorOrderRepository $oeOrderRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        MessageManagerInterface $messageManager,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        Helper $helper
    ) {
        $this->storeRepository              = $storeRepository;
        $this->quoteRepository              = $quoteRepository;
        $this->quoteItemToOrderItem         = $quoteItemToOrderItem;
        $this->quoteItemCollectionFactory   = $quoteItemCollectionFactory;
        $this->oeQuoteItemRepository        = $oeQuoteItemRepository;
        $this->oeOrderItemRepository        = $oeOrderItemRepository;
        $this->oeOrderRepository            = $oeOrderRepository;
        $this->productRepository            = $productRepository;
        $this->dataObjectFactory            = $dataObjectFactory;
        $this->messageManager               = $messageManager;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->helper                       = $helper;
    }

    /**
     * @param string[] $params
     * @param OrderEditorOrderModel $order
     * @return OriginalOrderItem[]
     * @throws \Exception
     */
    public function createNewOrderItems(
        array $params,
        OrderEditorOrderModel $order
    ): array {
        $params     = $this->prepareParams($params);
        $quoteItems = $this->prepareNewQuoteItems($params, $order);

        $orderItems = [];
        foreach ($quoteItems as $quoteItem) {
            try {
                $orderItem = $this->quoteItemToOrderItem->convert($quoteItem);
                $orderItem->setItemId($quoteItem->getItemId());
                $orderItem->setAppliedTaxes($quoteItem->getAppliedTaxes());

                if ($quoteItem->getProductType() == 'bundle') {
                    $simpleOrderItems = $this->addSimpleItemsForBundle($quoteItem, $orderItem);
                    $orderItem->setChildrenItems($simpleOrderItems);
                }

                if ($quoteItem->getProductType() == 'configurable') {
                    $orderItem->setData('product_options', $quoteItem->getProductOption());
                    $orderItem->setSku($quoteItem->getSku());
                }

                $orderItem->setMessage($quoteItem->getMessage());
                $orderItem->setHasError($quoteItem->getHasError());
                $orderItems[] = $orderItem;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $orderItems;
    }

    /**
     * @param int $itemId
     * @param string[] $params
     * @return OrderItemInterface
     * @throws LocalizedException
     */
    public function createNewOrderItem(int $itemId, array $params): OrderItemInterface
    {
        $orderItem   = $this->oeOrderItemRepository->getById($itemId);
        $params      = $this->prepareProductOptions($orderItem, $params);
        $quoteItem   = $this->convertOrderItemToQuoteItem($orderItem, $params);

        $quoteItem->isDeleted(false);
        if ($quoteItem->getChildren()) {
            foreach ($quoteItem->getChildren() as $childItem) {
                $childItem->isDeleted(false);
            }
        }

        $quoteItemId = $orderItem->getQuoteItemId();
        $quoteItem->setItemId($quoteItemId);

        $this->oeQuoteItemRepository->save($quoteItem);

        return $this->quoteItemToOrderItem->convert(
            $quoteItem,
            ['parent_item' => $orderItem]
        );
    }

    /**
     * @param OrderEditorQuoteItem $quoteItem
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface[]
     */
    protected function addSimpleItemsForBundle(
        OrderEditorQuoteItem $quoteItem,
        OrderItemInterface $orderItem
    ): array {
        $simpleOrderItems = [];
        $simpleQuoteItems = $quoteItem->getChildren();

        /** @var OriginalQuoteItem $simpleQuoteItem */
        foreach ($simpleQuoteItems as $simpleQuoteItem) {
            try {
                $simpleOrderItem = $this->quoteItemToOrderItem->convert($simpleQuoteItem);
                $simpleOrderItem->setItemId($simpleQuoteItem->getItemId());
                $simpleOrderItem->setParentItem($orderItem);

                $simpleOrderItem->setMessage($simpleQuoteItem->getMessage());
                $simpleOrderItem->setHasError($simpleQuoteItem->getHasError());
                $simpleOrderItem->setDiscountPercent($quoteItem->getDiscountPercent());
                $simpleOrderItems[] = $simpleOrderItem;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $simpleOrderItems;
    }

    /**
     * @param string[] $params
     * @param OrderEditorOrderModel $order
     * @return OrderEditorQuoteItem[]
     * @throws \Exception
     */
    protected function prepareNewQuoteItems(
        array $params,
        OrderEditorOrderModel $order
    ): array {
        $quoteItems = [];

        $quote = $this->getQuoteByOrder($order);
        $quote->setAllItemsAreNew(true); // @TODO why?

        // Prevent drop qty to null in case product is out of stock
        $quote->setIsSuperMode(true);
        $quote->setIgnoreOldQty(true);

        foreach ($params as $productId => $options) {
            try {
                $product   = $this->prepareProduct($productId, $order->getStore());
                $config    = $this->dataObjectFactory->create(['data' => $options]);
                $quoteItem = $quote->addProduct($product, $config);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                continue;
            }

            if (!empty($quoteItem)) {
                if (isset($options['bundle_option'])) {
                    $requestedOptions = count(
                        array_filter(
                            array_values($options['bundle_option']),
                            function ($value) {
                                return !empty($value) || $value === 0;
                            }
                        )
                    );

                    $addedOptions = count($quoteItem->getChildren());

                    if ($requestedOptions > $addedOptions) {
                        $quoteItem->setHasError(true);
                        $quoteItem->setMessage(
                            __(
                                'Not all selected products were added to the order as some products are currently unavailable.'
                            )
                        );
                    }
                }

                // Need to set quote id when quote is recreated
                $quoteItem->setQuote($quote)
                          ->setQuoteId($quote->getId());
                $this->oeQuoteItemRepository->save($quoteItem);
                foreach ($quoteItem->getChildren() as $child) {
                    $child->setProductType('simple');
                    $this->oeQuoteItemRepository->save($child);
                }
                $quoteItems[] = $quoteItem;
            }
        }

        $quote->collectTotals();

        /**
         * Fix for bundle products price calculation.
         * Without saving quote there was no row totals or base price saved
         * in the quote_item table.
         */
        $this->quoteRepository->save($quote);

        return $quoteItems;
    }

    /**
     * @param OriginalOrderItem $orderItem
     * @param string[] $params
     * @return OrderEditorQuoteItem
     * @throws LocalizedException
     */
    protected function convertOrderItemToQuoteItem(
        OriginalOrderItem $orderItem,
        array $params
    ): OrderEditorQuoteItem {
        $quoteItemId = $orderItem->getQuoteItemId();

        $quoteItem = $this->oeQuoteItemRepository->getById($quoteItemId);
        $quote     = $this->getQuoteByQuoteItem($quoteItem);

        $quoteItem->setQuote($quote);

        $dataObjectParams = $this->dataObjectFactory->create(['data' => $params]);
        $quoteItem        = $quote->updateItemAdvanced($quoteItem, $dataObjectParams);
        $quoteItem->isDeleted(false);
        if ($quoteItem->getChildren()) {
            foreach ($quoteItem->getChildren() as $childItem) {
                $childItem->isDeleted(false);
            }
        }

        $quote->collectTotals();

        return $quoteItem;
    }

    /**
     * @param int $productId
     * @param Store $store
     * @return Product
     * @throws LocalizedException
     */
    protected function prepareProduct(int $productId, Store $store): Product
    {
        /** @var Product $product */
        $product = $this->productRepository->getById($productId, false, $store->getStoreId());
        if (!$product->getId()) {
            throw new LocalizedException(
                __('An issue occurred when trying to add product ID %1 to the order.', $productId)
            );
        }

        return $product;
    }

    /**
     * @param string[] $params
     * @return string[]
     */
    protected function prepareParams(array $params): array
    {
        $preparedParams = [];

        foreach ($params as $productId => $options) {
            if (isset($options['super_group'])) {
                foreach ($options['super_group'] as $id => $opt) {
                    $qty = (float)$opt;
                    if ($qty > 0) {
                        $preparedParams[$id] = ['qty' => $qty];
                    }
                }
            } else {
                if (!empty($options)) {
                    $preparedParams[$productId] = $options;
                }
            }
        }

        return $preparedParams;
    }

    /**
     * @param OriginalOrderItem $orderItem
     * @param string[] $params
     * @return string[]
     */
    protected function prepareProductOptions(
        OriginalOrderItem $orderItem,
        array $params
    ): array {
        $params['product'] = $orderItem->getProductId();

        return $params;
    }

    /**
     * @param int $quoteItemId
     * @param array $params
     * @return OrderItemInterface
     * @throws \Exception
     * @throws LocalizedException
     */
    public function getUpdatedOrderItem(
        int $quoteItemId,
        array $params = []
    ): OrderItemInterface {
        $quoteItem = $this->getQuoteItemById($quoteItemId);
        $quote     = $this->getQuoteByQuoteItem($quoteItem);

        // Temporary Deactivate stock validation
        $oldSuperMode = $quote->getIsSuperMode();
        $quote->setIsSuperMode(true);

        $quoteItem->setQuote($quote);

        if (empty($params['qty'])) {
            $params['qty'] = 1;
        }

        $dataObjectParams = $this->dataObjectFactory->create(['data' => $params]);
        if (!$quoteItem->getId()) {
            throw new LocalizedException(__('Quote item must have ID before processing'));
        }
        $quoteItem = $quote->updateItemAdvanced($quoteItem, $dataObjectParams);
        if (!$quoteItem->getId()) {
            throw new LocalizedException(__('Quote item must have ID after update'));
        }
        $quoteItem->loadOptions();

        $quote->collectTotals();

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->quoteItemToOrderItem->convert($quoteItem);
        if ($quoteItem->getChildren()) {
            foreach ($quoteItem->getChildren() as $childQuoteItem) {
                $childOrderItem = $this->quoteItemToOrderItem->convert($childQuoteItem);
                $childOrderItem->setQuoteItemId($childQuoteItem->getId());
                $childOrderItem->setParentItemId($orderItem->getId());
                $childOrderItem->setParentItem($orderItem);
                $orderItem->addChildItem($childOrderItem);
                if ($orderItem->getProductType() !== 'bundle') {
                    $orderItem->setSku($childOrderItem->getSku());
                }
                $childOrderItems[] = $childOrderItem;
            }
        }

        $parentQuoteItemId = $quoteItem->getParentItemId();
        if ($parentQuoteItemId) {
            $parentOrderItemId = $this->getParentOrderItemId($parentQuoteItemId);
        }

        $orderItem->setOriginalPrice($orderItem->getPrice());
        $orderItem->setBaseOriginalPrice($orderItem->getBasePrice());
        $orderItem->setQuoteItemId($quoteItemId);
        if (!empty($parentOrderItemId)) {
            $orderItem->setParentItemId($parentOrderItemId);
        }

        // Activate stock validation
        $quote->setIsSuperMode($oldSuperMode);

        return $orderItem;
    }

    /**
     * @param int $parentQuoteItemId
     * @return int|null
     * @throws NoSuchEntityException
     */
    protected function getParentOrderItemId(int $parentQuoteItemId)
    {
        if ($parentQuoteItemId) {
            /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter('quote_item_id', $parentQuoteItemId);
            $searchCriteriaBuilder->setPageSize(1);
            $searchCriteriaBuilder->setCurrentPage(1);
            $searchCriteria = $searchCriteriaBuilder->create();

            $items = $this->oeOrderItemRepository->getList($searchCriteria);
            if ($items->getTotalCount() < 1) {
                throw new NoSuchEntityException(
                    __('There was no order item for the quote item with id %1', $parentQuoteItemId)
                );
            }

            $items     = $items->getItems();
            $orderItem = reset($items);

            return (int)$orderItem->getItemId();
        }

        return null;
    }

    /**
     * @param int $quoteItemId
     * @return OrderEditorQuoteItem
     * @throws LocalizedException
     */
    protected function getQuoteItemById(int $quoteItemId): OrderEditorQuoteItem
    {
        return $this->oeQuoteItemRepository->getById($quoteItemId);
    }

    /**
     * @param OriginalQuoteItem $quoteItem
     * @return OrderEditorQuoteModel
     * @throws LocalizedException
     */
    protected function getQuoteByQuoteItem(OriginalQuoteItem $quoteItem): OrderEditorQuoteModel
    {
        $storeId = $quoteItem->getStoreId();
        $store   = $this->storeRepository->getById($storeId);
        $quoteId = $quoteItem->getQuoteId();

        $this->quote = $this->quoteRepository->getById($quoteId)->setStore($store);

        return $this->quote;
    }

    /**
     * @param OrderEditorOrderModel $order
     * @return OrderEditorQuoteModel
     * @throws LocalizedException
     */
    protected function getQuoteByOrder(OrderEditorOrderModel $order): OrderEditorQuoteModel
    {
        $this->quote = $this->helper->getQuoteByOrder($order);

        return $this->quote; // @TODO Quote must be loaded
    }
}
