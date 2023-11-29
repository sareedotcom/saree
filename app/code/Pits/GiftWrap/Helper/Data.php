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
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Pits\GiftWrap\Model\GiftWrapData;

/**
 * Class Data
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
     * Configuration path to gift wrap title
     */
    const GIFT_WRAP_TITLE_CONFIG_PATH = 'pits_gift_wrap/general/title';

    /**
     * Configuration path to cart wrap title
     */
    const CART_WRAP_TITLE_CONFIG_PATH = 'pits_gift_wrap/general/cart_wrap_title';

    /**
     * Configuration path to cart wrap note
     */
    const GIFT_WRAP_CART_WRAP_NOTE_CONFIG_PATH = 'pits_gift_wrap/general/cart_wrap_note';

    /**
     * Configuration path to gift wrap fee label
     */
    const GIFT_WRAP_FEE_LABEL_CONFIG_PATH = 'pits_gift_wrap/general/fee_label';

    /**
     * Configuration path to gift wrap show summary
     */
    const GIFT_WRAP_SHOW_SUMMARY_CONFIG_PATH = 'pits_gift_wrap/general/show_summary';

    /**
     * Configuration path to gift wrap item note
     */
    const GIFT_WRAP_ITEM_NOTE_CONFIG_PATH = 'pits_gift_wrap/general/item_wrap_note';

    /**
     * Configuration path to gift message label
     */
    const GIFT_MESSAGE_LABEL_CONFIG_PATH = 'pits_gift_wrap/general/gift_message_label';

    /**
     * @var GiftWrapData
     */
    private $giftWrapData;

    /**
     * @var Registry
     */
    private $_coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Helper Data construct
     *
     * @param Context $context
     * @param GiftWrapData $giftWrapData
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        Context $context,
        GiftWrapData $giftWrapData,
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->giftWrapData = $giftWrapData;
        $this->_coreRegistry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
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
     * Get gift wrap title
     *
     * @return string
     */
    public function getGiftWrapTitle()
    {
        return $this->getConfig(self::GIFT_WRAP_TITLE_CONFIG_PATH);
    }

    /**
     * Get cart wrap title
     *
     * @return string
     */
    public function getCartWrapTitle()
    {
        return $this->getConfig(self::CART_WRAP_TITLE_CONFIG_PATH);
    }

    /**
     * Get gift wrap item note
     *
     * @return string
     */
    public function getItemWrapNote()
    {
        return $this->getConfig(self::GIFT_WRAP_ITEM_NOTE_CONFIG_PATH);
    }

    /**
     * Get cart wrap note
     *
     * @return string
     */
    public function getCartWrapNote()
    {
        return $this->getConfig(self::GIFT_WRAP_CART_WRAP_NOTE_CONFIG_PATH);
    }

    /**
     * Get gift wrap fee label
     *
     * @return string
     */
    public function getGiftWrapFeeLabel()
    {
        return $this->getConfig(self::GIFT_WRAP_FEE_LABEL_CONFIG_PATH);
    }

    /**
     * Get gift message label
     *
     * @return string
     */
    public function getGiftMessageLabel()
    {
        return $this->getConfig(self::GIFT_MESSAGE_LABEL_CONFIG_PATH);
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
        $storeId = ($storeId == null) ? $this->getStoreId() : $storeId;
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
     * Get show summary
     *
     * @return mixed
     */
    public function isShowSummary()
    {
        return $this->getConfig(self::GIFT_WRAP_SHOW_SUMMARY_CONFIG_PATH);
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
