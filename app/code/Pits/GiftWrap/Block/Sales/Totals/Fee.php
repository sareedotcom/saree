<?php

namespace Pits\GiftWrap\Block\Sales\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pits\GiftWrap\Model\Wrap;
use Pits\GiftWrap\Model\PriceCalculator;

/**
 * Class Fee
 *
 * @package Pits\GiftWrap\Block\Sales\Totals
 */
class Fee extends Template
{
    protected $_order;

    /**
     * @var DataObject
     */
    protected $_source;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * Fee constructor
     *
     * @param Context $context
     * @param PriceCalculator $priceCalculator
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        PriceCalculator $priceCalculator,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Check if we need display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Add gift wrap fee to the totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $fee = new DataObject(
            [
                'code' => Wrap::GIFT_WRAP_FEE_IDENTIFIER,
                'strong' => false,
                'value' => $this->getGiftWrapFee(true),
                'base_value' => $this->getGiftWrapFee(),
                'label' => $this->priceCalculator->getLabel(),
            ]
        );

        $parent->addTotal($fee, 'shipping');

        return $this;
    }

    /**
     * Get gift wrap fee
     *
     * @param false $store
     * @return float
     */
    public function getGiftWrapFee($store = false)
    {
         if ($this->getSource()->getGiftwrapFee() && $store) {
            return $this->getSource()->getGiftwrapFee();
        } elseif (!$store && $this->getSource()->getBaseGiftwrapFee()) {
            return $this->getSource()->getBaseGiftwrapFee();
        }
        
        return $this->priceCalculator->getOrderGiftWrapFee($this->getOrder(), $store);
    }

}
