<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin;

use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;

class DisableInventoryDetectionPlugin
{
    /**
     * @var InventoryDetectionStatusManager
     */
    protected $inventoryDetectionStatusManager;

    /**
     * DisableInventoryDetectionPlugin constructor.
     *
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     */
    public function __construct(InventoryDetectionStatusManager $inventoryDetectionStatusManager)
    {
        $this->inventoryDetectionStatusManager = $inventoryDetectionStatusManager;
    }

    /**
     * @param \Magento\InventorySalesApi\Api\IsProductSalableInterface $subject
     * @param callable $proceed
     * @param mixed ...$args
     * @return bool
     */
    public function aroundExecute($subject, $proceed, ...$args): bool
    {
        if ($this->inventoryDetectionStatusManager->isDisabledInventoryDetection()) {
            return true;
        }

        return $proceed(...$args);
    }
}
