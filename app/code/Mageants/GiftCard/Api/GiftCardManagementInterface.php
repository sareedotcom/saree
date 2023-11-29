<?php
namespace Mageants\GiftCard\Api;

interface GiftCardManagementInterface
{
    /**
     * Adds a gift card to a specified cart.
     *
     * @param int $cartId The cart ID.
     * @param string $giftCard The coupon code data.
     * @return bool
     */
    public function set($cartId, $giftCard);
}
