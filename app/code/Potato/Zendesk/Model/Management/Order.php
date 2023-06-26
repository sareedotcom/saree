<?php

namespace Potato\Zendesk\Model\Management;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Potato\Zendesk\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\UrlInterface;
use Potato\Zendesk\Model\Source\RedirectType;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Potato\Zendesk\Model\Source\RendererType;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class Order implements OrderManagementInterface
{
    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /** @var OrderItemRepositoryInterface  */
    protected $orderItemRepository;

    /** @var SearchCriteriaBuilder  */
    protected $searchCriteriaBuilder;

    /** @var CurrencyInterface  */
    protected $currency;

    /** @var AddressRenderer  */
    protected $addressRenderer;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /** @var TimezoneInterface  */
    protected $localeDate;

    /** @var RendererType  */
    protected $rendererType;

    /** @var ShipmentRepositoryInterface  */
    protected $shippmentRepository;

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param CurrencyInterface $currency
     * @param AddressRenderer $addressRenderer
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlInterface $urlBuilder
     * @param TimezoneInterface $localeDate
     * @param RendererType $rendererType
     * @param ShipmentRepositoryInterface $shippmentRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        CurrencyInterface $currency,
        AddressRenderer $addressRenderer,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        UrlInterface $urlBuilder,
        TimezoneInterface $localeDate,
        RendererType $rendererType,
        ShipmentRepositoryInterface $shippmentRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->addressRenderer = $addressRenderer;
        $this->urlBuilder = $urlBuilder;
        $this->localeDate = $localeDate;
        $this->rendererType = $rendererType;
        $this->shippmentRepository = $shippmentRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $orderIncrementId
     * @param int|Store|Website $scope
     * @return array
     * @throws NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function getInfo($orderIncrementId, $scope)
    {
        $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId, 'eq');
        if ($scope instanceof Website) {
            $this->searchCriteriaBuilder->addFilter('store_id', $scope->getStoreIds(), 'in');
        }
        if ($scope instanceof Store) {
            $this->searchCriteriaBuilder->addFilter('store_id', $scope->getId(), 'eq');
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();
        
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        $orderInfo = [];
        /** @var OrderInterface $order */
        foreach ($orderList as $order) {
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();
            if (!$shippingAddress) {
                $shippingAddress = $billingAddress;
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('order_id', $order->getEntityId(), 'eq')
                ->addFilter('parent_item_id', new \Zend_Db_Expr('null'), 'is')
                ->create();
            $orderItemsList = $this->orderItemRepository->getList($searchCriteria)->getItems();

            $currency = $this->currency->getCurrency($order->getBaseCurrencyCode());
            $orderItemInfo = [];
            /** @var OrderItemInterface $orderItem */
            foreach ($orderItemsList as $orderItem) {
                $redirectUrl = $this->urlBuilder->getUrl('po_zendesk/index/redirect',
                    ['id' => $orderItem->getProductId(), 'type' => RedirectType::PRODUCT_TYPE]);

                $renderer = $this->rendererType->getProductRendererByType($orderItem->getProductType());
                $renderer->setItem($orderItem)->setArea('frontend');

                $orderItemInfo[] = [
                    'url' => $redirectUrl,
                    'product_id' => $orderItem->getProductId(),
                    'name' => $orderItem->getName(),
                    'product_html' => $renderer->toHtml(),
                    'sku' => $orderItem->getSku(),
                    'price' => $currency->toCurrency($orderItem->getBasePrice()),
                    'ordered_qty' => (int)$orderItem->getQtyOrdered(),
                    'invoiced_qty' => (int)$orderItem->getQtyInvoiced(),
                    'shipped_qty' => (int)$orderItem->getQtyShipped(),
                    'refunded_qty' => (int)$orderItem->getQtyRefunded(),
                    'row_total' => $currency->toCurrency($orderItem->getBaseRowTotal())
                ];
            }

            $orderInfo = [
                'url' => $this->urlBuilder->getUrl('po_zendesk/index/redirect',
                    ['id' => $order->getEntityId(), 'type' => RedirectType::ORDER_TYPE]),
                'order_id' => $order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'store' => $this->storeManager->getStore($order->getStoreId())->getName(),
                'created_at' => $this->localeDate->formatDateTime($order->getCreatedAt(), \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::SHORT),
                'billing_address' => (null !== $billingAddress)?$this->addressRenderer->format($billingAddress, null):[],
                'shipping_address' => (null !== $shippingAddress)?$this->addressRenderer->format($shippingAddress, null):[],
                'payment_method' => $order->getPayment()->getMethodInstance()->getTitle(),
                'shipping_method' => $order->getShippingDescription(),
                'shipping_tracking' => $this->prepareShippingTrackingForOrder($order),
                'status' => $order->getStatusLabel(),
                'state' => $order->getState(),
                'totals' => [
                    'subtotal' => $currency->toCurrency($order->getBaseSubtotal()),
                    'shipping' => $currency->toCurrency($order->getBaseShippingAmount()),
                    'discount' => $currency->toCurrency($order->getBaseDiscountAmount()),
                    'tax' => $currency->toCurrency($order->getBaseTaxAmount()),
                    'grand_total' => $currency->toCurrency($order->getBaseGrandTotal())
                ],
                'items' => $orderItemInfo
            ];
        }
        return $orderInfo;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    private function prepareShippingTrackingForOrder(OrderInterface $order)
    {
        $shippingCollection = $order->getShipmentsCollection();
        $result = [];
        foreach ($shippingCollection as $shipmentItem) {
            try {
                $shipment = $this->shippmentRepository->get($shipmentItem->getId());
            } catch (NoSuchEntityException $e) {
                continue;
            }
            $trackList = $shipment->getAllTracks();
            foreach ($trackList as $track) {
                $carrier = $this->getCarrierName($track->getCarrierCode(), $order->getStoreId());
                $result[] = [
                    'carrier' => $carrier,
                    'number' => $track->getTrackNumber(),
                    'title' => $track->getTitle()
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $carrierCode
     * @param null|integer|Store $store
     * @return mixed
     */
    private function getCarrierName($carrierCode, $store = null)
    {
        if ($name = $this->scopeConfig->getValue(
            'carriers/' . $carrierCode . '/title',
            ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return $name;
        }
        return $carrierCode;
    }
}
