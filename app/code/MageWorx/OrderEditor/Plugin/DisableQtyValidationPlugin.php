<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Plugin;

use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use MageWorx\OrderEditor\Model\MsiStatusManager;

/**
 * Class DisableQtyValidationPlugin
 *
 * Disabling MSI stock qty validation, because the order editing is a super process.
 */
class DisableQtyValidationPlugin
{
    /**
     * @var MsiStatusManager
     */
    private $msiStatusManager;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * DisableQtyValidationPlugin constructor.
     *
     * @param MsiStatusManager $msiStatusManager
     * @param ObjectFactory $objectFactory
     */
    public function __construct(
        MsiStatusManager $msiStatusManager,
        ObjectFactory $objectFactory
    ) {
        $this->msiStatusManager = $msiStatusManager;
        $this->objectFactory    = $objectFactory;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param mixed ...$args
     * @return DataObject
     */
    public function aroundAroundCheckQuoteItemQty(
        $subject,
        \Closure $proceed,
        ...$args
    ) {
        if ($this->msiStatusManager->isMsiDisabled()) {
            $result = $this->objectFactory->create();
            $result->setHasError(false);

            return $result;
        }

        /** @var DataObject $result */
        $result = $proceed(...$args);

        return $result;
    }
}
