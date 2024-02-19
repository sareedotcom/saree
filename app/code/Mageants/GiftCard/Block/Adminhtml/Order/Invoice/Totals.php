<?php

namespace Mageants\GiftCard\Block\Adminhtml\Order\Invoice;

class Totals extends \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals
{
    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        
        if ($this->getOrder()->getOrderGift() != null) {
            $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'grand_total',
                    'strong' => true,
                    'value' => $this->getOrder()->getGrandTotal(),
                    'base_value' => $this->getOrder()->getBaseGrandTotal(),
                    'label' => __('Grand Total'),
                    'area' => 'footer',
                ]
            );
        }
        return $this;
    }
}
