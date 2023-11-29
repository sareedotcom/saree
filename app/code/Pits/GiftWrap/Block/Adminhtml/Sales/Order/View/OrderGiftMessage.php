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

namespace Pits\GiftWrap\Block\Adminhtml\Sales\Order\View;

use Exception;
use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Pits\GiftWrap\Model\GiftWrapData;

/**
 * Class OrderGiftMessage
 */
class OrderGiftMessage extends Template
{
    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * OrderGiftMessage constructor.
     *
     * @param Template\Context $context
     * @param GiftWrapData $giftWrapData
     * @param Registry $registry
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     * @return void
     */
    public function __construct(
        Template\Context $context,
        GiftWrapData $giftWrapData,
        Registry $registry,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data);
        $this->giftWrapData = $giftWrapData;
        $this->_coreRegistry = $registry;
    }

    /**
     * Retrieve available order
     *
     * @return Order
     * @throws LocalizedException
     */
    public function getOrder(): Order
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
     * Get gift wrap image path
     *
     * @return string
     */
    public function getGiftWrapImagePath(): string
    {
        return $this->giftWrapData->getGiftWrapImagePath();
    }

    /**
     * Check current order has a gift wrap for whole order
     *
     * @return bool
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
}
