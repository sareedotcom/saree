<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Logicrays\UpdateOrder\Controller\Adminhtml\Edit;

/**
 * Class Items
 */
class Items extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Items
{
    /**
     * @return void
     */
    protected function updateOrderItems()
    {
        $params = $this->getRequest()->getParams();

        $order = $this->getOrder();
        $order->editItems($params);

        $changeToReadyToDispatch = 1;
        foreach ($order->getAllVisibleItems() as $item) {
            if($item->getLrItemStatus() != 'ready_to_ship'){
                $changeToReadyToDispatch = 0;
            }
        }
        if($changeToReadyToDispatch){
            $order->setState($order->getState())->setStatus('ready_to_ship');
            $order->addStatusHistoryComment("");
            $order->save();
        }
    }
}
