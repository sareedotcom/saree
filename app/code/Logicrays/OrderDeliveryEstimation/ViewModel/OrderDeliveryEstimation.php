<?php

namespace Logicrays\OrderDeliveryEstimation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class OrderDeliveryEstimation implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Get helper data
     *
     * @return array
     */
    public function getHelperData()
    {
        return $this->helperData;
    }
}
