<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\AddressExtensionInterfaceFactory as QuoteAddressExtensionAttributesFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store as StoreModel;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface as CartRepositoryInterface;
use MageWorx\OrderEditor\Model\Config\Source\Shipments\UpdateMode;
use MageWorx\OrderEditor\Model\Order as OrderEditorOrderModel;
use MageWorx\OrderEditor\Model\Quote as OrderEditorQuoteModel;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * XML config return to stock
     */
    const XML_PATH_RETURN_TO_STOCK      = 'mageworx_order_management/order_editor/order_items/return_to_stock';
    const XML_PATH_INVOICE_UPDATE_MODE  =
        'mageworx_order_management/order_editor/invoice_shipment_refund/invoice_update_mode';
    const XML_PATH_SHIPMENT_UPDATE_MODE =
        'mageworx_order_management/order_editor/invoice_shipment_refund/shipments_update_mode';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var QuoteAddressExtensionAttributesFactory
     */
    protected $quoteAddressExtensionInterfaceFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param SerializerInterface $serializer
     * @param CartRepositoryInterface $cartRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param ManagerInterface $messageManager
     * @param QuoteAddressExtensionAttributesFactory $quoteAddressExtensionInterfaceFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SerializerInterface $serializer,
        CartRepositoryInterface $cartRepository,
        StoreRepositoryInterface $storeRepository,
        OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $messageManager,
        QuoteAddressExtensionAttributesFactory $quoteAddressExtensionInterfaceFactory
    ) {
        $this->coreRegistry      = $registry;
        $this->serializer        = $serializer;
        $this->cartRepository    = $cartRepository;
        $this->storeRepository   = $storeRepository;
        $this->orderRepository   = $orderRepository;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->messageManager    = $messageManager;

        $this->quoteAddressExtensionInterfaceFactory = $quoteAddressExtensionInterfaceFactory;

        parent::__construct($context);
    }

    /**
     * Get enable permanent order item removal
     *
     * @return bool
     */
    public function getReturnToStock(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_RETURN_TO_STOCK);
    }

    /**
     * Allow keep previous invoice and add new one
     *
     * @return bool
     */
    public function getIsAllowKeepPrevInvoice(): bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INVOICE_UPDATE_MODE) ==
            UpdateMode::MODE_UPDATE_ADD;
    }

    /**
     * Get update shipments mode
     *
     * @return string
     * @see    \MageWorx\OrderEditor\Model\Config\Source\Shipments\UpdateMode
     */
    public function getUpdateShipmentMode(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SHIPMENT_UPDATE_MODE);
    }

    /**
     * Get current order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('ordereditor_order');
    }

    /**
     * Set current order
     *
     * @param Order $order
     * @return void
     */
    public function setOrder(Order $order)
    {
        $this->coreRegistry->register('ordereditor_order', $order, true);
    }

    /**
     * Get current order entity id
     *
     * @return int|null
     */
    public function getOrderId()
    {
        if ($this->coreRegistry->registry('current_order')) {
            $order = $this->coreRegistry->registry('current_order');
        }
        if ($this->coreRegistry->registry('order')) {
            $order = $this->coreRegistry->registry('order');
        }

        if (isset($order)) {
            $orderId = (int)$order->getId();
        } else {
            $orderId = null;
        }

        return $orderId;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     * @throws LocalizedException
     */
    public function getQuote(): CartInterface
    {
        $quote = $this->coreRegistry->registry('ordereditor_quote');
        if (!$quote) {
            /** @var \MageWorx\OrderEditor\Model\Order $order */
            $order = $this->coreRegistry->registry('ordereditor_order');
            if (!$order) {
                throw new LocalizedException(
                    __('There is no Order in the registry')
                );
            }
            $quote = $this->getQuoteByOrder($order);
        }

        $this->coreRegistry->register('ordereditor_quote', $quote, true);

        return $quote;
    }

    /**
     * Set current quote
     *
     * @param CartInterface $quote
     * @return void
     */
    public function setQuote(CartInterface $quote)
    {
        if ($this->coreRegistry->registry('ordereditor_quote')) {
            $this->coreRegistry->unregister('ordereditor_quote');
        }

        $this->coreRegistry->register('ordereditor_quote', $quote);
    }

    /**
     * Retrieve customer identifier
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCustomerId()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new LocalizedException(
                __('There is no Order in the registry')
            );
        }

        return $order->getCustomerId() ? (int)$order->getCustomerId() : null;
    }

    /**
     * Retrieve store model object
     *
     * @return StoreInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->storeRepository->getById($this->getStoreId());
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     * @throws LocalizedException
     */
    public function getStoreId(): int
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new LocalizedException(
                __('There is no Order in the registry')
            );
        }

        return (int)$order->getStoreId();
    }

    /**
     * Round and format price
     *
     * @return string
     */
    public function roundAndFormatPrice($price): string
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * @param mixed $value
     * @return array|bool|float|int|string|null
     */
    public function decodeBuyRequestValue($value)
    {
        return $this->unserialize($value);
    }

    /**
     * @param string $value
     * @return array|bool|float|int|string|null
     */
    public function unserialize(string $value)
    {
        return $this->serializer->unserialize($value);
    }

    /**
     * @param array $value
     * @return bool|string
     */
    public function encodeBuyRequestValue($value)
    {
        return $this->serialize($value);
    }

    /**
     * @param array $value
     * @return string|bool
     */
    public function serialize(array $value)
    {
        return $this->serializer->serialize($value);
    }

    /**
     * @param OrderEditorOrderModel $order
     * @return OrderEditorQuoteModel
     * @throws LocalizedException
     */
    public function getQuoteByOrder(OrderEditorOrderModel $order): OrderEditorQuoteModel
    {
        $storeId = $order->getStoreId();
        /** @var StoreModel $store */
        $store   = $this->storeRepository->getById($storeId);
        $quoteId = $order->getQuoteId();

        if ($quoteId) {
            try {
                $quote = $this->cartRepository->getById($quoteId)
                                              ->setStore($store);
            } catch (NoSuchEntityException $exception) {
                $quote = $this->recreateEmptyQuote($order);
            }
        } else {
            $quote = $this->recreateEmptyQuote($order);
        }

        return $quote;
    }

    /**
     * Create new quote with empty items and shipping address
     *
     * @param OrderEditorOrderModel $order
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function recreateEmptyQuote(OrderEditorOrderModel $order): CartInterface
    {
        $storeId = $order->getStoreId();
        /** @var StoreModel $store */
        $store = $this->storeRepository->getById($storeId);

        $quote = $this->cartRepository->getEmptyEntity()
                                      ->setStore($store);

        if (!$order->getIsVirtual()) {
            $shippingAddress      = $quote->getShippingAddress();
            $orderShippingAddress = $order->getShippingAddress();
            if ($orderShippingAddress) {
                $orderShippingAddressData = $orderShippingAddress->getData();
                unset($orderShippingAddressData['entity_id']);
                $orderShippingAddressData['extension_attributes'] =
                    $this->quoteAddressExtensionInterfaceFactory->create();
                $shippingAddress->addData($orderShippingAddressData);
                $quote->setShippingAddress($shippingAddress);
            }
        }

        $billingAddress      = $quote->getBillingAddress();
        $orderBillingAddress = $order->getBillingAddress();
        if ($orderBillingAddress) {
            $orderBillingAddressData = $orderBillingAddress->getData();
            unset($orderBillingAddressData['entity_id']);
            $orderBillingAddressData['extension_attributes'] =
                $this->quoteAddressExtensionInterfaceFactory->create();
            $billingAddress->addData($orderBillingAddressData);
            $quote->setBillingAddress($billingAddress);
        }

        $orderItems             = $order->getAllVisibleItems();
        $quoteItemsToOrderItems = [];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $quoteItem = $this->initFromOrderItem($orderItem, $quote);
            if ($quoteItem) {
                $quoteItemsToOrderItems[$orderItem->getId()] = [
                    'quote_item' => $quoteItem,
                    'order_item' => $orderItem
                ];
            }
        }

        $quote->collectTotals();

        $this->cartRepository->save($quote);
        $order->setQuoteId($quote->getId());

        $this->assignNewQuoteItemIdsToOrderItems($quoteItemsToOrderItems);
        if (!$order->getIsVirtual() && isset($shippingAddress) && $shippingAddress->getId()) {
            $order->getShippingAddress()->setQuoteAddressId($shippingAddress->getId());
        }

        if ($billingAddress->getId()) {
            $orderBillingAddress->setQuoteAddressId($billingAddress->getId());
        }

        $this->orderRepository->save($order);

        return $quote;
    }

    /**
     * In case a quote is recreating for the order we will assign new quote item ids for the old order items
     *
     * @param array $quoteItemsToOrderItems
     * @throws LocalizedException
     */
    private function assignNewQuoteItemIdsToOrderItems(array $quoteItemsToOrderItems): void
    {
        foreach ($quoteItemsToOrderItems as $data) {
            $orderItem = $data['order_item'];
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            $quoteItem = $data['quote_item'];
            $orderItem->setQuoteItemId($quoteItem->getId());
            $this->reAssignQuoteItemChildrenToOrderItem($orderItem, $quoteItem);
        }
    }

    /**
     * re-Assign child items
     *
     * @param Order\Item $orderItem
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @throws LocalizedException
     */
    private function reAssignQuoteItemChildrenToOrderItem(
        \Magento\Sales\Model\Order\Item $orderItem,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        if (!$orderItem->getChildrenItems() && !$quoteItem->getChildren()) {
            return;
        }

        if ($orderItem->getChildrenItems() && !$quoteItem->getChildren()) {
            throw new LocalizedException(
                __(
                    'Quote item (%1) has no children items, but order item (%2) has.',
                    $quoteItem->getId(),
                    $orderItem->getId()
                )
            );
        }

        if ($quoteItem->getChildren() && !$orderItem->getChildrenItems()) {
            throw new LocalizedException(
                __(
                    'Order item (%1) has no children items, but quote item (%2) has.',
                    $orderItem->getId(),
                    $quoteItem->getId()
                )
            );
        }

        $quoteItemIdsBySku = $this->getQuoteItemIdsBySku($quoteItem);

        /** @var \Magento\Sales\Model\Order\Item $childOrderItem */
        foreach ($orderItem->getChildrenItems() as $childOrderItem) {
            $childOrderItemSku = $childOrderItem->getSku();
            $childQuoteItemId  = $quoteItemIdsBySku[$childOrderItemSku] ?? null;
            if (!$childQuoteItemId) {
                throw new LocalizedException(
                    __(
                        'Unable to find a corresponding child quote item for order item with id %1',
                        $childOrderItem->getId()
                    )
                );
            }

            $childOrderItem->setQuoteItemId($childQuoteItemId);
        }
    }

    /**
     * Collect quote item's ids by sku
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return array
     * @throws LocalizedException
     */
    private function getQuoteItemIdsBySku(\Magento\Quote\Model\Quote\Item $quoteItem): array
    {
        $idsBySku = [];
        foreach ($quoteItem->getChildren() as $childQuoteItem) {
            $productSku = $childQuoteItem->getProduct()->getSku();
            if (!empty($idsBySku[$productSku])) {
                throw new LocalizedException(
                    __(
                        'Child products with same sku "%1" detected in one quote item %2',
                        $productSku,
                        $quoteItem->getId()
                    )
                );
            }
            $idsBySku[$productSku] = $childQuoteItem->getId();
        }

        return $idsBySku;
    }

    /**
     * Initialize creation data from existing order Item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote\Item|bool
     * @throws LocalizedException
     */
    public function initFromOrderItem(
        \Magento\Sales\Model\Order\Item $orderItem,
        \Magento\Quote\Model\Quote $quote
    ) {
        if (!$orderItem->getId()) {
            return false; // or must throw an exception?
        }

        try {
            $product = $this->createProduct($orderItem->getProductId(), $quote->getStoreId());
        } catch (NoSuchEntityException $noSuchEntityException) {
            $this->messageManager->addErrorMessage(
                __(
                    'Unable to load product with id %1 for store %2',
                    $orderItem->getProductId(),
                    $quote->getStoreId()
                )
            );

            return false; // or must throw an exception?
        }

        $product->setSkipCheckRequiredOption(true);
        $buyRequest = $orderItem->getBuyRequest();
        if (!$buyRequest->getQty()) {
            $buyRequest->setQty(1);
        }

        $item = $quote->addProduct(
            $product,
            $buyRequest,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE
        );

        // In case of error
        if (is_string($item)) {
            return false; // or must throw an exception?
        }

        $additionalOptions = $orderItem->getProductOptionByCode('additional_options');
        if ($additionalOptions) {
            $item->addOption(
                $this->dataObjectFactory->create(
                    [
                        'data' => [
                            'product' => $item->getProduct(),
                            'code'    => 'additional_options',
                            'value'   => $this->serializer->serialize($additionalOptions)
                        ]
                    ]
                )
            );
        }

        $this->_eventManager->dispatch(
            'sales_convert_order_item_to_quote_item',
            ['order_item' => $orderItem, 'quote_item' => $item]
        );

        return $item;
    }

    /**
     * @param $productId
     * @param $storeId
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    private function createProduct(int $productId, int $storeId = null): ProductInterface
    {
        /** @var ProductInterface|Product $product */
        return $this->productRepository->getById($productId, false, $storeId);
    }
}
