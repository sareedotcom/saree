<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin;

use MageWorx\OrderEditor\Model\MsiStatusManager;

/**
 * Class DisableBackordersQtyValidationPlugin
 *
 * Disabling MSI stock qty validation for backorders, because the order editing is a super process.
 */
class DisableBackordersQtyValidationPlugin
{
    /**
     * @var MsiStatusManager
     */
    private $msiStatusManager;

    /**
     * DisableQtyValidationPlugin constructor.
     *
     * @param MsiStatusManager $msiStatusManager
     */
    public function __construct(
        MsiStatusManager $msiStatusManager
    ) {
        $this->msiStatusManager = $msiStatusManager;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param mixed ...$args
     * @return void
     */
    public function aroundExecute(
        $subject,
        \Closure $proceed,
        ...$args
    ) {
        if ($this->msiStatusManager->isMsiDisabled()) {
            return;
        }

        return $proceed(...$args);
    }
}
