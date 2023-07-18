<?php
namespace Logicrays\OrderCancellation\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Data implements ArgumentInterface
{
    /**
     *
     * @var Data
     */
    protected $priceHelper;

    /**
     *
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(\Magento\Framework\Pricing\Helper\Data $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     *
     * Gets price in format
     */
    public function getPriceFormat($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}
