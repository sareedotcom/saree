<?php
namespace Logicrays\OrderCancellation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Page\Config;
use Logicrays\OrderCancellation\Helper\Data;

class CancelOrder extends Template
{
    /**
     *
     * @var Data
     */
    protected $helper;

    /**
     *
     * @param Context $context
     * @param Config $pageConfig
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $pageConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->pageConfig = $pageConfig;
        $this->helper = $helper;
    }

    /**
     *
     * Gets order id
     */
    public function getOrderNumber()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        return $order_id;
    }

    /**
     *
     * Checking extension is enable or not
     */
    public function modEnable()
    {
        $value = $this->helper->modEnable();
        return $value;
    }

    /**
     *
     * Gets order updated date
     */

    public function getDateForComplete()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orderRepository->get($orderId);
        $updated_date = $order->getUpdatedAt();
        return $updated_date;
    }

    /**
     *
     * Gets order status
     */
    public function getOrderStatus()
    {
        $orderId = $this->getOrderNumber();
        $order = $this->orderRepository->get($orderId);
        $state = $order->getStatus();
        return $state;
    }

    /**
     *
     * Gets button name
     */
    public function getLinkText()
    {
        return 'Cancellation Request';
    }
}
