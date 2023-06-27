<?php

namespace Logicrays\OrderDeliveryEstimation\Plugin\Quote;

class ConvertToOrderItem
{
    /**
     * Convert to order item
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     * @return void
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);
        $orderItem->setEstdDispatchDate($item->getEstdDispatchDate());
        return $orderItem;
    }
}
