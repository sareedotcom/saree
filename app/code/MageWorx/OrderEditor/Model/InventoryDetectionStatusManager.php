<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model;

class InventoryDetectionStatusManager
{
    const ENABLE  = 1;
    const DISABLE = 2;

    /**
     * @var int
     */
    private $status = self::ENABLE;

    /**
     * @return bool
     */
    public function isDisabledInventoryDetection()
    {
        return $this->status === self::DISABLE;
    }

    /**
     * @return void
     */
    public function disableInventoryDetection()
    {
        $this->status = self::DISABLE;
    }

    /**
     * @return void
     */
    public function enableInventoryDetection()
    {
        $this->status = self::ENABLE;
    }
}
