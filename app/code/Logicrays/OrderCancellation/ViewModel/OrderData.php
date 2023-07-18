<?php
namespace Logicrays\OrderCancellation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class OrderData implements ArgumentInterface
{
    /**
     *
     * @var Data
     */
    protected $priceHelper;

    /**
     *
     * @var Order
     */
    protected $orders;

    /**
     *
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\Order $orders
     */
    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\Order $orders
    ) {
        $this->priceHelper = $priceHelper;
        $this->orders = $orders;
    }

    /**
     *
     * Gets price in format
     */
    public function getPriceFormat($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     *
     * Gets Order Cancellation Reason
     */
    public function getCancellationReason($oid)
    {
        $order = $this->orders->load($oid);
        return $order->getOrderCancellationReason();
    }
}
