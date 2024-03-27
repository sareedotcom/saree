<?php

namespace Logicrays\TermsAndCondition\Block\Adminhtml\Order\View;

use Magento\Framework\Registry;

class View extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
    }

    public function getOrderCustom()
    {
        $order = $this->_coreRegistry->registry('current_order');
        return $order->getTermsAndConditionIsAccepted() ? $order->getTermsAndConditionIsAccepted() : "-";
    }
}