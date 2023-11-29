<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

namespace Pits\GiftWrap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Pits\GiftWrap\Model\GiftWrapData;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

/**
 * Class Data
 *
 * @package Pits\GiftWrap\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Configuration path to gift wrap module status
     */
    const MODULE_STATUS_CONFIG_PATH = 'pits_gift_wrap/general/enable';

    /**
     * Configuration path to gift wrap fee
     */
    const GIFT_WRAP_FEE_CONFIG_PATH = 'pits_gift_wrap/general/fee';

    /**
     * @var GiftWrapData
     */
    private $giftWrapData;
    
    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * Helper Data construct
     *
     * @param Context $context
     * @param GiftWrapData $giftWrapData
     * @param Registry $registry
     * @return void
     */
    public function __construct(
        Context $context,
        GiftWrapData $giftWrapData,
        Registry $registry

    ) {
        parent::__construct($context);
        $this->giftWrapData = $giftWrapData;
        $this->_coreRegistry = $registry;
    }

    /**
     * Get gift wrap price
     *
     * @return float
     */
    public function getGiftWrapUnitPrice(): float
    {
        return $this->getConfig(self::GIFT_WRAP_FEE_CONFIG_PATH);
    }

    /**
     * Get configuration value
     *
     * @param string $field
     * @param null|int $storeId
     * @return mixed
     */
    public function getConfig(string $field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Module status
     *
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->getConfig(self::MODULE_STATUS_CONFIG_PATH);
    }

    /**
     * Retrieve available order
     *
     * @return Order
     * @throws LocalizedException
     */
    public function getOrder()
    {
        if ($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }
        if ($this->_coreRegistry->registry('order')) {
            return $this->_coreRegistry->registry('order');
        }
        throw new LocalizedException(__('We can\'t get the order instance right now.'));
    }

    /**
     * Get Order gift wrap message
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function getGiftMessageByOrder(): ?string
    {
        try {
            if ($order = $this->getOrder()) {
                return $this->giftWrapData->getGiftMessageByOrder($order);
            }
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * Check current order has a gift wrap for whole order
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isGiftWrapOrder(): bool
    {
        try {
            return (bool)$this->giftWrapData->getGiftWrapModelByOrder($this->getOrder());
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return false;
    }


    /**
     * Gift wrap message of a order item
     *
     * @param $item
     * @return string|null
     */
    public function getGiftMessageByItem($item): ?string
    {
        return $this->giftWrapData->getGiftMessageByItem($item);
    }

    /**
     * Get gift wrap image path
     *
     * @return string
     */
    public function getGiftWrapImagePath(): string
    {
        return $this->giftWrapData->getGiftWrapImagePath();
    }

    /**
     * Check current item is a gift wrap item
     *
     * @param $item
     * @return bool
     */
    public function isGiftWrapItem($item): bool
    {
        return $this->giftWrapData->isGiftWrapItem($item);
    }
}
