<?php

declare(strict_types=1);

namespace Logicrays\CancelProcess\Plugin\Block\Widget\Button;

use Magento\Sales\Block\Adminhtml\Order\Create;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Sales\Api\OrderRepositoryInterface;

class ToolbarPlugin
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Add button on sales order view page
     *
     * @param \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return void
     */
    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        if ($this->_request->getFullActionName() == 'sales_order_view' || $this->_request->getFullActionName() == 'lrdes_order_view') {
            $order_id = $context->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($order_id);
            if ($order->getState() == 'canceled' && $order->getStatus() == 'canceled') {
                $url = $context->getUrl('cancel/process/index', ['order_id' => $order_id]);
                $buttonList->add(
                    'convert_order ',
                    ['label' => __('Convert order'),
                    'on_click' => sprintf("location.href = '%s';", $url),
                    'class' => 'reset'],
                    -1
                );
            }
        }
    }
}
