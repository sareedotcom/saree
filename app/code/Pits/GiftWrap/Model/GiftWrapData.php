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

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Pits\GiftWrap\Api\Data\WrapInterface;
use Pits\GiftWrap\Api\WrapRepositoryInterface;

/**
 * Class GiftWrapData
 */
class GiftWrapData extends AbstractModel
{
    /**
     * @var WrapRepositoryInterface
     */
    protected $wrapRepository;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var Repository
     */
    protected $assetRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * GiftWrapData constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param WrapRepositoryInterface $wrapRepository
     * @param Json $jsonSerializer
     * @param Repository $assetRepository
     * @param RequestInterface $request
     * @param PriceCalculator $priceCalculator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        Registry $registry,
        WrapRepositoryInterface $wrapRepository,
        Json $jsonSerializer,
        Repository $assetRepository,
        RequestInterface $request,
        PriceCalculator $priceCalculator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->wrapRepository = $wrapRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Get gift wrap data from order
     *
     * @param Order $order
     * @return string|null
     */
    public function getGiftWrapData(Order $order): ?string
    {
        return $order->getGiftWrapData();
    }

    /**
     * Get gift wrap model for order item
     *
     * @param Item $item
     * @return WrapInterface|null
     */
    public function getGiftWrapModel(Item $item): ?WrapInterface
    {
        try {
            if ($giftWrapData = $this->getGiftWrapData($item->getOrder())) {
                $giftWrapDataArray = $this->jsonSerializer->unserialize($giftWrapData);
                $orderItems = $giftWrapDataArray['items'] ?? [];
                if ($orderItems) {
                    foreach ($orderItems as $itemId => $orderGiftWrapId) {
                        // Since we are copying data from quote table to sales_order table there maybe a chance that
                        // quote id and order item id can be different
                        if ($itemId == $item->getQuoteItemId()) {
                            return $this->wrapRepository->getById($orderGiftWrapId);
                        }
                    }
                }
            }
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Get gift wrap model for an whole order
     *
     * @param Order $order
     * @return WrapInterface|null
     */
    public function getGiftWrapModelByOrder(Order $order): ?WrapInterface
    {
        try {
            if ($giftWrapData = $this->getGiftWrapData($order)) {
                $giftWrapDataArray = $this->jsonSerializer->unserialize($giftWrapData);
                $giftItemId = $giftWrapDataArray['whole_cart'] ?? null;
                if ($giftItemId) {
                    return $this->wrapRepository->getById($giftItemId);
                }
            }
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Gift wrap message of a order item
     *
     * @param Item $item
     * @return string|null
     */
    public function getGiftMessageByItem(Item $item): ?string
    {
        if ($giftWrapModel = $this->getGiftWrapModel($item)) {
            return $giftWrapModel->getMessage();
        }

        return null;
    }

    /**
     * Get gift Message for an whole order
     *
     * @param Order $order
     * @return string|null
     */
    public function getGiftMessageByOrder(Order $order): ?string
    {
        if ($giftWrapModel = $this->getGiftWrapModelByOrder($order)) {
            return $giftWrapModel->getMessage();
        }

        return null;
    }

    /**
     * Get Gift wrap image path for admin area
     *
     * @return string
     */
    public function getGiftWrapImagePath(): string
    {
        $params = [
            'area'    => 'adminhtml',
            '_secure' => $this->request->isSecure(),
        ];

        return $this->assetRepository->getUrlWithParams('Pits_GiftWrap::images/gift-wrap.png', $params);
    }

    /**
     * Check if a gift wrap item
     *
     * @param Item $item
     * @return bool
     */
    public function isGiftWrapItem(Item $item): bool
    {
        return (bool)$this->getGiftWrapModel($item);
    }

    /**
     * Get Gift wrap amount for the order only
     *
     * @param Order $order
     * @return float
     */
    public function getGiftWrapAmountByOrder(Order $order): float
    {
        if ($giftWrapModel = $this->getGiftWrapModelByOrder($order)) {
            return $giftWrapModel->getPrice();
        }

        return 0;
    }

    /**
     * Get final invoice fee
     *
     * @param Order $order
     * @param array $invoiceItems
     * @param false $store
     * @return float|int
     */
    public function getFinalInvoiceFee(Order $order, array $invoiceItems = [], $store = false)
    {
        $fee = 0;
        try {
            $fee = $this->priceCalculator->getOrderGiftWrapFee($order, $store);
            $orderGiftFeeData = $this->getOrderGiftFeeData($order, $store);
            $fee = $fee - $orderGiftFeeData['invoicedFee'] + $orderGiftFeeData['refundedFee']
                + $orderGiftFeeData['cancelledFee'];
            if ($invoiceItems) {
                foreach ($invoiceItems as $itemId => $qty) {
                    $item = $order->getItemById($itemId);
                    if ($giftWrapModel = $this->getGiftWrapModel($item)) {
                        if ($item->getQtyRefunded() == 0
                            && $item->getQtyCanceled() == 0
                            && $item->getQtyInvoiced() == 0
                            && $item->getQtyShipped() == 0
                            && $qty == 0) {
                            if ($store) {
                                $fee = $fee - $giftWrapModel->getStorePrice();
                            } else {
                                $fee = $fee - $giftWrapModel->getPrice();
                            }
                        }
                    }
                }
            }
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $fee;
    }

    /**
     * Get final refund fee
     *
     * @param Order $order
     * @param array $creditmemoItems
     * @param bool $store
     * @return float|int|mixed
     */
    public function getFinalRefundFee(Order $order, array $creditmemoItems = [], $store = false)
    {
        $maximumRefundableFee = 0;
        try {
            // If order is not completely refunded, order wrap fee cannot be refunded
            $isFullOrderRefund = true;
            $totalOrderFee = $this->priceCalculator->getOrderGiftWrapFee($order, $store);
            $orderGiftFeeData = $this->getOrderGiftFeeData($order, $store);
            if ($orderGiftFeeData['invoicedFee'] < ($totalOrderFee - $orderGiftFeeData['refundedFee']
                    - $orderGiftFeeData['cancelledFee'])) {
                $isFullOrderRefund = false;
            }
            $maximumRefundableFee =
                $orderGiftFeeData['invoicedFee'] - $orderGiftFeeData['refundedFee'] - $orderGiftFeeData['cancelledFee'];

            $shippedItemsData = $this->getShippedItemsGiftFee($order, $store);
            if ($shippedItemsData['isFullOrderRefund'] == false) {
                $isFullOrderRefund = false;
                $maximumRefundableFee -= $shippedItemsData['shippedItemsGiftFee'];
            }

            $nonShippedItemsData = $this->getNonSelectedGiftFee($order, $creditmemoItems, $store);
            if ($nonShippedItemsData['isFullOrderRefund'] == false) {
                $isFullOrderRefund = false;
                $maximumRefundableFee -= $nonShippedItemsData['shippedItemsGiftFee'];
            }

            if (!$isFullOrderRefund) {
                $maximumRefundableFee -= $this->getGiftWrapAmountByOrder($order);
            }
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $maximumRefundableFee;
    }

    /**
     * Get order gift fee data
     *
     * @param Order $order
     * @param false $store
     * @return array
     */
    public function getOrderGiftFeeData(Order $order, $store = false)
    {
        $orderGiftFeeData = [];
        if ($store) {
            $orderGiftFeeData['invoicedFee'] = $order->getGiftWrapInvoiced() ?? 0;
            $orderGiftFeeData['refundedFee'] = $order->getGiftWrapRefunded() ?? 0;
            $orderGiftFeeData['cancelledFee'] = $order->getGiftWrapCancelled() ?? 0;
        } else {
            $orderGiftFeeData['invoicedFee'] = $order->getBaseGiftWrapInvoiced() ?? 0;
            $orderGiftFeeData['refundedFee'] = $order->getBaseGiftWrapRefunded() ?? 0;
            $orderGiftFeeData['cancelledFee'] = $order->getBaseGiftWrapCancelled() ?? 0;
        }

        return $orderGiftFeeData;
    }

    /**
     * Get shipped items gift fee for credit memo
     *
     * @param Order $order
     * @param null|int $store
     * @return array
     */
    public function getShippedItemsGiftFee(Order $order, $store = null)
    {
        $shippedItemsGiftFee = 0;
        $isFullOrderRefund = true;
        foreach ($order->getAllItems() as $item) {
            //Already shipped item gift fee does not need to be refunded
            if ($item->getQtyShipped() > 0) {
                if ($giftWrapModel = $this->getGiftWrapModel($item)) {
                    if ($store) {
                        $shippedItemsGiftFee += $giftWrapModel->getStorePrice();
                    } else {
                        $shippedItemsGiftFee += $giftWrapModel->getPrice();
                    }
                }
                $isFullOrderRefund = false;
            }
        }

        return ['shippedItemsGiftFee' => $shippedItemsGiftFee, 'isFullOrderRefund' => $isFullOrderRefund];
    }

    /**
     * get Non selected items gift fee for credit memo
     *
     * @param Order $order
     * @param array $creditmemoItems
     * @param false $store
     * @return array
     */
    public function getNonSelectedGiftFee(Order $order, $creditmemoItems, bool $store = false): array
    {
        $nonSelectedGiftFee = 0;
        $isFullOrderRefund = true;
        if ($creditmemoItems) {
            foreach ($creditmemoItems as $itemId => $data) {
                $item = $order->getItemById($itemId);
                if ($giftWrapModel = $this->getGiftWrapModel($item)) {
                    if ($item->getQtyShipped() == 0 && $data['qty'] == 0 && $item->getQtyInvoiced() > 0) {
                        if ($store) {
                            $nonSelectedGiftFee += $giftWrapModel->getStorePrice();
                        } else {
                            $nonSelectedGiftFee += $giftWrapModel->getPrice();
                        }
                    }
                } else {
                    $isFullOrderRefund = false;
                }
            }
        }

        return ['nonSelectedGiftFee' => $nonSelectedGiftFee, 'isFullOrderRefund' => $isFullOrderRefund];
    }

    /**
     * Get order cancelled gift fee
     *
     * @param Order $order
     * @return float|int
     */
    public function getOrderCancelledGiftFee(Order $order)
    {
        $invoicedFee = $order->getGiftWrapInvoiced() ?? 0;
        $refundedFee = $order->getGiftWrapRefunded() ?? 0;

        return $this->priceCalculator->getOrderGiftWrapFee($order) - $invoicedFee - $refundedFee;
    }
}
