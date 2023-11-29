<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model;

/**
 * Class MsiStatusManager
 */
class MsiStatusManager
{
    /**
     * @var bool
     */
    private $isMsiDisabled = false;

    /**
     * @return void
     */
    public function disableMSI()
    {
        $this->isMsiDisabled = true;
    }

    /**
     * @return void
     */
    public function enableMSI()
    {
        $this->isMsiDisabled = false;
    }

    /**
     * @return bool
     */
    public function isMsiDisabled(): bool
    {
        return (bool)$this->isMsiDisabled;
    }
}
