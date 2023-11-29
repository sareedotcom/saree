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

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order\Item;
use Pits\GiftWrap\Model\GiftWrapData;

/**
 * Class GiftOptions
 */
class GiftOptions extends Template
{
    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * GiftOptions constructor.
     *
     * @param Template\Context $context
     * @param GiftWrapData $giftWrapData
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     * @return void
     */
    public function __construct(
        Template\Context $context,
        GiftWrapData $giftWrapData,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data);
        $this->giftWrapData = $giftWrapData;
    }

    /**
     * Get order item object from parent block
     *
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->getParentBlock()->getData('item');
    }

    /**
     * Gift wrap message of a order item
     *
     * @return string|null
     */
    public function getGiftMessageByItem(): ?string
    {
        return $this->giftWrapData->getGiftMessageByItem($this->getItem());
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
     * @return bool
     */
    public function isGiftWrapItem(): bool
    {
        return $this->giftWrapData->isGiftWrapItem($this->getItem());
    }
}
