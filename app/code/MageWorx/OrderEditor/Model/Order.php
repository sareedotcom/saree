<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model;

use Exception;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Sales\Model\Order as OriginalOrder;
use Magento\Sales\Model\Order\Item as OriginalOrderItem;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory as TaxCollectionFactory;
use MageWorx\OrderEditor\Api\OrderItemRepositoryInterface as OrderEditorOrderItemRepository;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteItemRepositoryInterface as OrderEditorQuoteItemRepository;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface as QuoteRepositoryInterface;
use MageWorx\OrderEditor\Model\Order\Item as OrderEditorOrderItem;

/**
 * Class Order
 */
class Order extends OriginalOrder
{
    /**
     * @var []
     */
    protected $newParams = [];

    /**
     * @var \Magento\Tax\Model\Config $taxConfig
     */
    protected $taxConfig = null;

    /**
     * @var \MageWorx\OrderEditor\Model\Order\Sales
     */
    protected $sales;

    /**
     * @var float
     */
    protected $oldTotal;

    /**
     * @var float
     */
    protected $oldQtyOrdered;

    /**
     * @var []
     */
    protected $addedItems = [];

    /**
     * @var []
     */
    protected $removedItems = [];

    /**
     * @var []
     */
    protected $increasedItems = [];

    /**
     * @var []
     */
    protected $decreasedItems = [];

    /**
     * @var []
     */
    protected $changesInAmounts = [];

    /**
     * @var \MageWorx\OrderEditor\Model\Quote
     */
    protected $quote;

    /**
     * @var \MageWorx\OrderEditor\Model\Invoice
     */
    protected $invoice;

    /**
     * @var \MageWorx\OrderEditor\Model\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \MageWorx\OrderEditor\Model\Shipment
     */
    protected $shipment;

    /**
     * @var TaxCollectionFactory
     */
    protected $taxCollectionFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderEditorQuoteItemRepository
     */
    protected $oeQuoteItemRepository;

    /**
     * @var OrderEditorOrderItemRepository
     */
    protected $oeOrderItemRepository;

    /**
     * Order constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OriginalOrder\Config $orderConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param OriginalOrder\Status\HistoryFactory $orderHistoryFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param Quote $quote
     * @param Order\Sales $sales
     * @param QuoteRepositoryInterface $quoteRepository
     * @param Invoice $invoice
     * @param Shipment $shipment
     * @param Creditmemo $creditmemo
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderEditorQuoteItemRepository $oeQuoteItemRepository
     * @param OrderEditorOrderItemRepository $oeOrderItemRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param ManagerInterface $messageManager
     * @param OrderCollectionFactoryBox $collectionFactoryBox
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Model\Config $taxConfig,
        \MageWorx\OrderEditor\Model\Order\Sales $sales,
        QuoteRepositoryInterface $quoteRepository,
        \MageWorx\OrderEditor\Model\Invoice $invoice,
        \MageWorx\OrderEditor\Model\Shipment $shipment,
        \MageWorx\OrderEditor\Model\Creditmemo $creditmemo,
        OrderRepositoryInterface $orderRepository,
        OrderEditorQuoteItemRepository $oeQuoteItemRepository,
        OrderEditorOrderItemRepository $oeOrderItemRepository,
        DataObjectFactory $dataObjectFactory,
        ManagerInterface $messageManager,
        OrderCollectionFactoryBox $collectionFactoryBox,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        // Models
        $this->taxConfig  = $taxConfig;
        $this->sales      = $sales;
        $this->invoice    = $invoice;
        $this->creditmemo = $creditmemo;
        $this->shipment   = $shipment;

        // Repositories
        $this->quoteRepository       = $quoteRepository;
        $this->orderRepository       = $orderRepository;
        $this->oeQuoteItemRepository = $oeQuoteItemRepository;
        $this->oeOrderItemRepository = $oeOrderItemRepository;

        // Collections & Collection Factories
        $this->taxCollectionFactory = $collectionFactoryBox->getTaxCollectionFactory();

        // Utility
        $this->dataObjectFactory = $dataObjectFactory;
        $this->messageManager    = $messageManager;

        // Unpack Collection Factories from the Box
        $orderItemCollectionFactory  = $collectionFactoryBox->getOrderItemCollectionFactory();
        $addressCollectionFactory    = $collectionFactoryBox->getAddressCollectionFactory();
        $paymentCollectionFactory    = $collectionFactoryBox->getPaymentCollectionFactory();
        $historyCollectionFactory    = $collectionFactoryBox->getHistoryCollectionFactory();
        $invoiceCollectionFactory    = $collectionFactoryBox->getInvoiceCollectionFactory();
        $shipmentCollectionFactory   = $collectionFactoryBox->getShipmentCollectionFactory();
        $memoCollectionFactory       = $collectionFactoryBox->getMemoCollectionFactory();
        $trackCollectionFactory      = $collectionFactoryBox->getTrackCollectionFactory();
        $salesOrderCollectionFactory = $collectionFactoryBox->getSalesOrderCollectionFactory();
        $productListFactory          = $collectionFactoryBox->getProductListFactory();

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data
        );

        // Overwrite collection factories: objects returned must be instance of the OrderEditor classes
        $this->_invoiceCollectionFactory  = $collectionFactoryBox->getOeInvoiceCollectionFactory();
        $this->_memoCollectionFactory     = $collectionFactoryBox->getOeCreditmemoCollectionFactory();
        $this->_shipmentCollectionFactory = $collectionFactoryBox->getOeShipmentCollectionFactory();
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\MageWorx\OrderEditor\Model\ResourceModel\Order::class);
    }

    /**
     * @return bool
     */
    public function hasItemsWithIncreasedQty(): bool
    {
        return array_sum($this->increasedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasItemsWithDecreasedQty(): bool
    {
        return array_sum($this->decreasedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasAddedItems(): bool
    {
        return count($this->addedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasRemovedItems(): bool
    {
        return count($this->removedItems) > 0;
    }

    /**
     * @return bool
     */
    public function hasChangesInAmounts(): bool
    {
        return count($this->changesInAmounts) > 0;
    }

    /**
     * @return bool
     */
    public function isTotalWasChanged(): bool
    {
        return $this->getChangesInTotal() != 0;
    }

    /**
     * @return float
     */
    public function getChangesInTotal(): float
    {
        return (float)$this->oldTotal - (float)$this->getCurrentOrderTotal();
    }

    /**
     * @return float
     */
    protected function getCurrentOrderTotal(): float
    {
        return (float)$this->getGrandTotal() - (float)$this->getTotalRefunded();
    }

    /**
     * @return void
     */
    protected function resetChanges()
    {
        $this->oldTotal         = $this->getCurrentOrderTotal();
        $this->oldQtyOrdered    = $this->getTotalQtyOrdered();
        $this->addedItems       = [];
        $this->removedItems     = [];
        $this->increasedItems   = [];
        $this->decreasedItems   = [];
        $this->changesInAmounts = [];
    }

    /**
     * @param string[] $params
     * @return void
     * @throws Exception
     */
    public function editItems(array $params)
    {
        $this->resetChanges();
        $this->prepareParamsForEditItems($params);
        $this->updateOrderItems();
        $this->collectOrderTotals();
        $this->updatePayment();
    }

    /**
     * @return void
     */
    public function updatePayment()
    {
        $this->sales->setOrder($this)->updateSalesObjects();
    }

    /**
     * @param string[] $params
     * @return void
     * @throws LocalizedException
     */
    protected function prepareParamsForEditItems(array $params)
    {
        if (!isset($params['order_id']) || !isset($params['item'])) {
            throw new LocalizedException(__('Incorrect params for edit order items'));
        }

        $this->newParams = $params['item'];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function updateOrderItems()
    {
        foreach ($this->newParams as $id => $params) {
            if ($params['item_type'] === 'quote') {
                $id = null;
            }

            if (!empty($params['parent'])) {
                continue;
            }

            $item = $this->loadOrderItem($id, $params);
            /* var $item \MageWorx\OrderEditor\Model\Order\Item */
            $orderItem = $item->editItem($params, $this);

            $this->collectItemsChanges($orderItem);
        }
    }

    /**
     * @param int|null $id
     * @param string[] $params
     * @return OrderEditorOrderItem
     * @throws NoSuchEntityException
     */
    protected function loadOrderItem(int $id = null, array $params = []): OrderEditorOrderItem
    {
        $item = $this->oeOrderItemRepository->getEmptyEntity();

        // @TODO Seems like it must be !isset($params['item_type']) || $params['item_type'] !== 'quote'
        if (!isset($params['item_type']) || $params['item_type'] !== 'quote') {
            if (isset($params['action']) && $params['action'] == 'remove') {
                $this->removedItems[] = $id;
            }
            if ($id) {
                $item = $this->oeOrderItemRepository->getById($id);
            } elseif (!empty($params['item_id']) && !empty($params['parent'])) {
                $quoteItemId = (int)(str_ireplace('q', '', $params['parent']));
                $item = $this->oeOrderItemRepository->getByQuoteItemId($quoteItemId);
            }
        }

        return $item;
    }

    /**
     * @param OriginalOrderItem|OrderEditorOrderItem $orderItem
     * @return void
     */
    protected function collectItemsChanges(OriginalOrderItem $orderItem)
    {
        $itemId                        = $orderItem->getItemId();
        $this->increasedItems[$itemId] = $orderItem->getIncreasedQty();
        $this->decreasedItems[$itemId] = $orderItem->getDecreasedQty();

        $changes = $orderItem->getChangesInAmounts();
        if (!empty($changes)) {
            $this->changesInAmounts[$itemId] = $changes;
        }
    }

    /**
     * @param int $id
     * @param string[] $params
     * @return void
     * @throws Exception
     */
    protected function editNewItem(int $id, array $params)
    {
        if (isset($params['item_type']) && $params['item_type'] == 'quote') {
            $this->addedItems[] = $id;

            unset($params['action'], $params['item_type']);

            $item = $this->oeOrderItemRepository->getById($id);
            $item->editItem($params, $this);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function collectOrderTotals()
    {
        $totalQtyOrdered                   = 0;
        $weight                            = 0;
        $totalItemCount                    = 0;
        $baseDiscountTaxCompensationAmount = 0;
        $baseDiscountAmount                = 0;
        $baseTotalWeeeDiscount             = 0;
        $baseSubtotal                      = 0;
        $baseSubtotalInclTax               = 0;

        /** @var OrderEditorOrderItem $orderItem */
        foreach ($this->getItemsCollection() as $orderItem) {
            $baseDiscountAmount += $orderItem->getBaseDiscountAmount();

            //bundle part
            if ($orderItem->getParentItem()) {
                continue;
            }

            $baseDiscountTaxCompensationAmount += $orderItem->getBaseDiscountTaxCompensationAmount();

            $totalQtyOrdered += $orderItem->getQtyOrdered();
            $totalItemCount++;
            $weight                += $orderItem->getRowWeight();
            $baseSubtotal          += $orderItem->getBaseRowTotal(); /* RowTotal for item is a subtotal */
            $baseSubtotalInclTax   += $orderItem->getBaseRowTotalInclTax();
            $baseTotalWeeeDiscount += $orderItem->getBaseDiscountAppliedForWeeeTax();
        }

        /* convert currency */
        $baseCurrencyCode  = $this->getBaseCurrencyCode();
        $orderCurrencyCode = $this->getOrderCurrencyCode();

        if ($baseCurrencyCode === $orderCurrencyCode) {
            $discountAmount                = $baseDiscountAmount + $this->getBaseShippingDiscountAmount();
            $discountTaxCompensationAmount = $baseDiscountTaxCompensationAmount;
            $subtotal                      = $baseSubtotal;
            $subtotalInclTax               = $baseSubtotalInclTax;
        } else {
            $discountAmount                = $this->getBaseCurrency()
                                                  ->convert(
                                                      $baseDiscountAmount + $this->getBaseShippingDiscountAmount(),
                                                      $orderCurrencyCode
                                                  );
            $discountTaxCompensationAmount = $this->getBaseCurrency()
                                                  ->convert(
                                                      $baseDiscountTaxCompensationAmount,
                                                      $orderCurrencyCode
                                                  );
            $subtotal                      = $this->getBaseCurrency()
                                                  ->convert(
                                                      $baseSubtotal,
                                                      $orderCurrencyCode
                                                  );
            $subtotalInclTax               = $this->getBaseCurrency()
                                                  ->convert(
                                                      $baseSubtotalInclTax,
                                                      $orderCurrencyCode
                                                  );
        }

        $this->setTotalQtyOrdered($totalQtyOrdered)
             ->setWeight($weight)
             ->setSubtotal($subtotal)->setBaseSubtotal($baseSubtotal)
             ->setSubtotalInclTax($subtotalInclTax)
             ->setBaseSubtotalInclTax($baseSubtotalInclTax)
             ->setDiscountTaxCompensationAmount($discountTaxCompensationAmount)
             ->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensationAmount)
             ->setDiscountAmount('-' . $discountAmount)
             ->setBaseDiscountAmount('-' . $baseDiscountAmount)
             ->setTotalItemCount($totalItemCount);

        $this->calculateGrandTotal();

        $this->orderRepository->save($this);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function calculateGrandTotal()
    {
        $this->reCalculateTaxAmount();

        // shipping tax
        $tax     = $this->getTaxAmount() + $this->getShippingTaxAmount();
        $baseTax = $this->getBaseTaxAmount() + $this->getBaseShippingTaxAmount();

        $this->setTaxAmount($tax)->setBaseTaxAmount($baseTax);
        $this->orderRepository->save($this);

        // Order GrandTotal include tax
        if ($this->checkTaxConfiguration()) {
            $grandTotal     = $this->getSubtotal()
                + $this->getTaxAmount()
                + $this->getShippingAmount()
                - abs($this->getDiscountAmount())
                - abs($this->getGiftCardsAmount())
                - abs($this->getCustomerBalanceAmount());
            $baseGrandTotal = $this->getBaseSubtotal()
                + $this->getBaseTaxAmount()
                + $this->getBaseShippingAmount()
                - abs($this->getBaseDiscountAmount())
                - abs($this->getBaseGiftCardsAmount())
                - abs($this->getBaseCustomerBalanceAmount());
        } else {
            $grandTotal     = $this->getSubtotalInclTax()
                + $this->getShippingInclTax()
                - abs($this->getDiscountAmount())
                - abs($this->getGiftCardsAmount())
                - abs($this->getCustomerBalanceAmount());
            $baseGrandTotal = $this->getBaseSubtotalInclTax()
                + $this->getBaseShippingInclTax()
                - abs($this->getBaseDiscountAmount())
                - abs($this->getBaseGiftCardsAmount())
                - abs($this->getBaseCustomerBalanceAmount());
        }

        $this->setGrandTotal($grandTotal)
             ->setBaseGrandTotal($baseGrandTotal);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function reCalculateTaxAmount()
    {
        $baseTaxAmount = 0;

        /**
         * @var OrderEditorOrderItem $orderItem
         */
        foreach ($this->getItemsCollection() as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }
            $baseTaxAmount += $orderItem->getBaseTaxAmount();
        }

        $baseCurrencyCode  = $this->getBaseCurrencyCode();
        $orderCurrencyCode = $this->getOrderCurrencyCode();
        if ($baseCurrencyCode === $orderCurrencyCode) {
            $taxAmount = $baseTaxAmount;
        } else {
            $taxAmount = $this->getBaseCurrency()->convert(
                $baseTaxAmount,
                $orderCurrencyCode
            );
        }

        $this->setTaxAmount($taxAmount)->setBaseTaxAmount($baseTaxAmount);
    }

    /**
     * @return bool
     */
    public function checkTaxConfiguration(): bool
    {
        $catalogPrices         = $this->taxConfig->priceIncludesTax() ? 1 : 0;
        $shippingPrices        = $this->taxConfig->shippingPriceIncludesTax() ? 1 : 0;
        $applyTaxAfterDiscount = $this->taxConfig->applyTaxAfterDiscount() ? 1 : 0;

        return !$catalogPrices && !$shippingPrices && $applyTaxAfterDiscount;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function syncQuote()
    {
        if ($this->hasItemsWithIncreasedQty()
            || $this->hasAddedItems()
            || $this->hasItemsWithDecreasedQty()
            || $this->hasRemovedItems()
            || $this->isTotalWasChanged()
        ) {
            $this->syncQuoteItems();
        }

        $this->syncAddressesData();
        $this->syncQuoteData();

        return $this;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function syncQuoteData()
    {
        $additionalData = [
            'store_to_base_rate'          => $this->getStoreToBaseRate(),
            'store_to_quote_rate'         => $this->getStoreToOrderRate(),
            'base_currency_code'          => $this->getBaseCurrencyCode(),
            'store_currency_code'         => $this->getStoreCurrencyCode(),
            'quote_currency_code'         => $this->getOrderCurrencyCode(),
            'grand_total'                 => $this->getGrandTotal(),
            'base_grand_total'            => $this->getBaseGrandTotal(),
            'subtotal'                    => $this->getSubtotal(),
            'base_subtotal'               => $this->getBaseSubtotal(),
            'subtotal_with_discount'      => $this->getSubtotal()
                - abs($this->getDiscountAmount()),
            'base_subtotal_with_discount' => $this->getBaseSubtotal()
                - abs($this->getBaseDiscountAmount()),
            'items_qty'                   => $this->getTotalQtyOrdered(),
            'items_count'                 => $this->getTotalItemCount()
        ];

        $this->getQuote()->addData($additionalData);
        $this->quoteRepository->save($this->getQuote());
    }

    /**
     * @return void
     */
    protected function syncQuoteItems()
    {
        $orderItems = $this->getItems();
        foreach ($orderItems as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();
            if (!$quoteItemId) {
                continue;
            }

            try {
                $quoteItem = $this->oeQuoteItemRepository->getById($quoteItemId);
                $quoteItem->setQuote($this->getQuote());
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->_logger->critical($noSuchEntityException);
                continue;
            }

            $qty = $orderItem->getQtyOrdered()
                - $orderItem->getQtyRefunded()
                - $orderItem->getQtyCanceled();

            $data = [
                'product_id'                            => $orderItem->getProductId(),
                'store_id'                              => $orderItem->getStoreId(),
                'is_virtual'                            => $orderItem->getIsVirtual(),
                'sku'                                   => $orderItem->getSku(),
                'name'                                  => $orderItem->getName(),
                'description'                           => $orderItem->getDescription(),
                'additional_data'                       => $orderItem->getAdditionalData(),
                'applied_rule_ids'                      => $orderItem->getAppliedRuleIds(),
                'is_qty_decimal'                        => $orderItem->getIsQtyDecimal(),
                'no_discount'                           => $orderItem->getNoDiscount(),
                'weight'                                => $orderItem->getWeight(),
                'qty'                                   => $qty,
                'price'                                 => $orderItem->getPrice(),
                'base_price'                            => $orderItem->getBasePrice(),
                'custom_price'                          => $orderItem->getPrice(),
                'discount_percent'                      => $orderItem->getDiscountPercent(),
                'discount_amount'                       => $orderItem->getDiscountAmount(),
                'base_discount_amount'                  => $orderItem->getBaseDiscountAmount(),
                'tax_percent'                           => $orderItem->getTaxPercent(),
                'base_tax_amount'                       => $orderItem->getBaseTaxAmount(),
                'row_total'                             => $orderItem->getRowTotal(),
                'base_row_total'                        => $orderItem->getBaseRowTotal(),
                'row_total_with_discount'               => $orderItem->getRowTotal() - $orderItem->getDiscountAmount(),
                'row_weight'                            => $orderItem->getRowWeight(),
                'product_type'                          => $orderItem->getProductType(),
                'base_tax_before_discount'              => $orderItem->getBaseTaxBeforeDiscount(),
                'tax_before_discount'                   => $orderItem->getTaxBeforeDiscount(),
                'original_custom_price'                 => $orderItem->getOriginalPrice(),
                'base_cost'                             => $orderItem->getBaseCost(),
                'price_incl_tax'                        => $orderItem->getPriceInclTax(),
                'base_price_incl_tax'                   => $orderItem->getBasePriceInclTax(),
                'row_total_incl_tax'                    => $orderItem->getRowTotalInclTax(),
                'base_row_total_incl_tax'               => $orderItem->getBaseRowTotalInclTax(),
                'discount_tax_compensation_amount'      => $orderItem->getDiscountTaxCompensationAmount(),
                'base_discount_tax_compensation_amount' => $orderItem->getBaseDiscountTaxCompensationAmount(),
                'free_shipping'                         => $orderItem->getFreeShipping(),
                'weee_tax_applied'                      => $orderItem->getWeeeTaxApplied(),
                'weee_tax_applied_amount'               => $orderItem->getWeeeTaxAppliedAmount(),
                'weee_tax_applied_row_amount'           => $orderItem->getWeeeTaxAppliedRowAmount(),
                'weee_tax_disposition'                  => $orderItem->getWeeeTaxDisposition(),
                'weee_tax_row_disposition'              => $orderItem->getWeeeTaxRowDisposition(),
                'base_weee_tax_applied_amount'          => $orderItem->getBaseWeeeTaxAppliedAmount(),
                'base_weee_tax_applied_row_amnt'        => $orderItem->getBaseWeeeTaxAppliedRowAmnt(),
                'base_weee_tax_disposition'             => $orderItem->getBaseWeeeTaxDisposition(),
                'base_weee_tax_row_disposition'         => $orderItem->getBaseWeeeTaxRowDisposition(),
            ];

            $quoteItem->addData($data);

            try {
                $this->oeQuoteItemRepository->save($quoteItem);
            } catch (LocalizedException $e) {
                $this->messageManager
                    ->addErrorMessage(
                        __('Something goes wrong while sync quote items. Original error message: %1', $e->getMessage())
                    );
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function syncAddressesData()
    {
        $additionalData = [
            'quote_id'                                     => $this->getQuoteId(),
            'weight'                                       => $this->getWeight(),
            'subtotal'                                     => $this->getSubtotal(),
            'base_subtotal'                                => $this->getBaseSubtotal(),
            'subtotal_with_discount'                       => $this->getSubtotal()
                - abs($this->getDiscountAmount()),
            'base_subtotal_with_discount'                  => $this->getBaseSubtotal()
                - abs($this->getBaseDiscountAmount()),
            'tax_amount'                                   => $this->getTaxAmount(),
            'base_tax_amount'                              => $this->getBaseTaxAmount(),
            'shipping_amount'                              => $this->getShippingAmount(),
            'base_shipping_amount'                         => $this->getBaseShippingAmount(),
            'shipping_tax_amount'                          => $this->getShippingTaxAmount(),
            'base_shipping_tax_amount'                     => $this->getBaseShippingTaxAmount(),
            'discount_amount'                              => $this->getDiscountAmount(),
            'base_discount_amount'                         => $this->getBaseDiscountAmount(),
            'grand_total'                                  => $this->getGrandTotal(),
            'base_grand_total'                             => $this->getBaseGrandTotal(),
            'shipping_discount_amount'                     => $this->getShippingDiscountAmount(),
            'base_shipping_discount_amount'                => $this->getBaseShippingDiscountAmount(),
            'subtotal_incl_tax'                            => $this->getSubtotalInclTax(),
            'base_subtotal_total_incl_tax'                 => $this->getBaseSubtotalInclTax(),
            'discount_tax_compensation_amount'             => $this->getDiscountTaxCompensationAmount(),
            'base_discount_tax_compensation_amount'        => $this->getBaseDiscountTaxCompensationAmount(),
            'shipping_discount_tax_compensation_amount'    => $this->getShippingDiscountTaxCompensationAmount(),
            'base_shipping_discount_tax_compensation_amnt' => $this->getBaseShippingDiscountTaxCompensationAmnt(),
            'shipping_incl_tax'                            => $this->getShippingAmount()
                + $this->getShippingTaxAmount(),
            'base_shipping_incl_tax'                       => $this->getBaseShippingAmount()
                + $this->getBaseShippingTaxAmount()
        ];

        if (!$this->getIsVirtual()) {
            $shippingAddressAdditionalData     = [
                'shipping_method'      => $this->getShippingMethod(false),
                'shipping_description' => $this->getShippingDescription()
            ];
            $shippingAddressData               = $this->getShippingAddress()->getData();
            $quoteAddress                      = $this->getQuote()->getShippingAddress();
            $shippingAddressData['address_id'] = $quoteAddress->getAddressId();
            $finalShippingData                 = array_merge(
                $shippingAddressData,
                $additionalData,
                $shippingAddressAdditionalData
            );

            $quoteAddress->addData($finalShippingData);
            $quoteAddress->save();
        }

        $billingAddressData               = $this->getBillingAddress()->getData();
        $quoteAddress                     = $this->getQuote()->getBillingAddress();
        $billingAddressData['address_id'] = $quoteAddress->getAddressId();
        $finalBillingData                 = array_merge($billingAddressData, $additionalData);

        $quoteAddress->addData($finalBillingData);
        $quoteAddress->save();
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeDelete()
    {
        $this->deleteRelatedShipments();
        $this->deleteRelatedInvoices();
        $this->deleteRelatedCreditMemos();
        $this->deleteRelatedOrderInfo();

        parent::beforeDelete();

        return $this;
    }

    /**
     * @return void
     */
    protected function deleteRelatedOrderInfo()
    {
        try {
            $collection = $this->_addressCollectionFactory->create()->setOrderFilter($this);
            $collection->walk('delete');

            $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($this);
            $collection->walk('delete');

            $collection = $this->_paymentCollectionFactory->create()->setOrderFilter($this);
            $collection->walk('delete');

            $collection = $this->_historyCollectionFactory->create()->setOrderFilter($this);
            $collection->walk('delete');

            $collection = $this->taxCollectionFactory->create()->loadByOrder($this);
            $collection->walk('delete');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Delete related order info error: %1', $e->getMessage()));
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedInvoices()
    {
        try {
            $collection = $this->getInvoiceCollection();
            $collection->walk('delete');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Delete related invoices error: %1', $e->getMessage()));
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedShipments()
    {
        try {
            $collection = $this->getShipmentsCollection();
            $collection->walk('delete');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Delete related shipments error: %1', $e->getMessage()));
        }
    }

    /**
     * @return void
     */
    protected function deleteRelatedCreditMemos()
    {
        try {
            $collection = $this->getCreditmemosCollection();
            $collection->walk('delete');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Delete related credit memos error: %1', $e->getMessage()));
        }
    }

    /**
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        if ($this->quote) {
            return $this->quote;
        }

        $this->quote = $this->quoteRepository->getById($this->getQuoteId());

        return $this->quote;
    }
}
