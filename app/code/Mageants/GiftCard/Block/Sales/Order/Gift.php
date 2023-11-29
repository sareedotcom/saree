<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Sales\Order;

use \Magento\Framework\View\Element\Template\Context;

/**
 * Sale order Gift class
 */
class Gift extends \Magento\Framework\View\Element\Template
{
    /**
     * Check if we nedd display full tax total info
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
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }
    /**
     * Get store
     *
     * @return \Magento\Framework\DataObject
     */
     
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Return Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * To Get label properties from parent block
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * To Get Value properties from parent block
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $store = $this->getStore();
        
        if ($this->getOrder()->getOrderGift() != null) {

            $fee = new \Magento\Framework\DataObject(
                [
                        'code' => 'giftcertificate',
                        'strong' => false,
                        'value' => -$this->_order->getOrderGift(),
                        'label' => __('GiftCard'),
                    ]
            );

            $parent->addTotal($fee, 'giftcertificate');
            $parent->addTotal($fee, 'giftcertificate');
            return $this;
        }
    }
}
