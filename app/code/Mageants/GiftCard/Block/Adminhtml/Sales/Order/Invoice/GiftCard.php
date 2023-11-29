<?php

namespace Mageants\GiftCard\Block\Adminhtml\Sales\Order\Invoice;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Tax\Model\Config;
use \Mageants\GiftCard\Helper\Data;

class GiftCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Config
     */
    protected $_config;
    /**
     * @var order
     */
    protected $_order;
    /**
     * @var source
     */
    protected $_source;
    /**
     * @var Data
     */
    protected $helperdata;
    
    /**
     * @param Context $context
     * @param Config $taxConfig
     * @param Data $helperdata
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $taxConfig,
        Data $helperdata,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        $this->helperdata = $helperdata;
        parent::__construct($context, $data);
    }

    /**
     * To Display full Summary
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Return Source
     */
    public function getSource()
    {
        return $this->_source;
    }
    
    /**
     * Retuen store from order
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Retuen order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Return to Label properties
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Return to value properties
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Return Init total
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();
        if ($this->_order->getOrderGift() != null) {
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'GiftCard',
                    'strong' => false,
                    'value' => -$this->_order->getOrderGift(),
                    // 'base_value' => $this->_order->getFee(),
                    'label' => __('GiftCard'),
                ]
            );
             $parent->addTotal($fee, 'GiftCard');
             $parent->addTotal($fee, 'GiftCard');
        }
           return $this;
    }
}
