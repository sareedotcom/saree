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

namespace Pits\GiftWrap\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class OrderManagementInterfacePlugin
 */
class OrderManagementInterfacePlugin
{
    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * OrderManagementInterfacePlugin constructor.
     *
     * @param Wrap $wrap
     * @return void
     */
    public function __construct(
        Wrap $wrap
    ) {
        $this->wrap = $wrap;
    }

    /**
     * After plugin for update gift wrap store price on DB
     *
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $result)
    {
        if ($quoteWrapData = $result->getData(Wrap::GIFT_WRAP_FIELD_NAME)) {
            $this->wrap->setStoreGiftWrapPrice($quoteWrapData);
        }

        return $result;
    }
}
