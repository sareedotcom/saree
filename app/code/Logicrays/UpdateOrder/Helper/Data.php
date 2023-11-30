<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Logicrays\UpdateOrder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Sales\Model\OrderRepository;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Data constructor.
     *
     * @param Context $context
     * @param OrderStatusCollection $orderStatusCollection,
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        OrderStatusCollection $orderStatusCollection,
        OrderRepository $orderRepository
    ) {
        $this->orderStatusCollection=$orderStatusCollection;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * Get Order Status
     * @return array
     */
    public function getOrderStatus($orderId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $this->orderRepository->get($orderId);
        return $order->getConfig()->getStateStatuses($order->getState());
        // return $this->orderStatusCollection->toOptionArray(); // All Status
    }
}
