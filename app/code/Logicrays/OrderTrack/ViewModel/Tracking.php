<?php

namespace Logicrays\OrderTrack\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Tracking implements ArgumentInterface
{
    /**
     * @var \Magento\Shipping\Helper\Data
     */
    private $shippingHelper;

    /**
     * Tracking constructor.
     * @param \Magento\Shipping\Helper\Data $shippingHelper
     */
    public function __construct(\Magento\Shipping\Helper\Data $shippingHelper)
    {
        $this->shippingHelper = $shippingHelper;
    }

    public function getTrackingPopupUrlBySalesModel($order)
    {
        return $this->shippingHelper->getTrackingPopupUrlBySalesModel($order);
    }
}