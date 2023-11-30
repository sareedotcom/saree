<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin;

class DisableMSI
{
    /**
     * @var \MageWorx\OrderEditor\Model\MsiStatusManager
     */
    private $msiStatusManager;

    /**
     * DisableMSI constructor.
     *
     * @param \MageWorx\OrderEditor\Model\MsiStatusManager $msiStatusManager
     */
    public function __construct(
        \MageWorx\OrderEditor\Model\MsiStatusManager $msiStatusManager
    ) {
        $this->msiStatusManager = $msiStatusManager;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param mixed ...$args
     * @return bool
     */
    public function aroundExecute($subject, $proceed, ...$args): bool
    {
        if ($this->msiStatusManager->isMsiDisabled()) {
            return true;
        }

        return $proceed(...$args);
    }
}
