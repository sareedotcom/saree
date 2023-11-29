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

namespace Pits\GiftWrap\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Interface WrapInterface
 *
 * @api
 * @package Pits\GiftWrap\Api\Data
 */
interface WrapInterface extends CustomAttributesDataInterface
{
    /**
     * Order gift wrap table name
     */
    const GIFT_WRAP_TABLE_NAME = 'order_gift_wrap';

    /**
     * Gift wrap id column
     */
    const GIFT_WRAP_ID_COLUMN = 'id';

    /**
     * Gift wrap message column
     */
    const GIFT_WRAP_MESSAGE_COLUMN = 'message';

    /**
     * Gift wrap price column
     */
    const GIFT_WRAP_PRICE_COLUMN = 'price';

    /**
     * Gift wrap belongs to whole order column
     */
    const GIFT_WRAP_BELONGS_TO_WHOLE_ORDER_COLUMN = 'is_whole_order';

    /**
     * Quote table gift wrap data whole cart array identifier
     */
    const QUOTE_GIFT_WRAP_DATA_WHOLE_CART_IDENTIFIER = 'whole_cart';

    /**
     * Quote table gift wrap data cart item array identifier
     */
    const QUOTE_ITEMS_GIFT_WRAP_DATA_IDENTIFIER = 'items';

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get Price
     *
     * @return int
     */
    public function getPrice();

    /**
     * Set Price
     *
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get is whole cart gift wrap
     *
     * @return int
     */
    public function getIsWholeOrder();

    /**
     * Set is whole cart gift wrap
     *
     * @param int $isWholeOrder
     * @return $this
     */
    public function setIsWholeOrder($isWholeOrder);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Pits\GiftWrap\Api\Data\WrapExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Pits\GiftWrap\Api\Data\WrapExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Pits\GiftWrap\Api\Data\WrapExtensionInterface $extensionAttributes);
}
