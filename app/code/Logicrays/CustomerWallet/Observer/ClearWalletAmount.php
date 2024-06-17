<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Observer;

class ClearWalletAmount implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Cart Items variable
     *
     * @var Cart
     */
    private $cartItems;

    /**
     * __construct function
     *
     * @param \Magento\Checkout\Model\Cart $cartItems
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cartItems
    ) {
        $this->cartItems = $cartItems;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // removing wallet amount when customer can remove item from cart
        $quote = $this->cartItems->getQuote();
        $quote->setWalletamount(0);
        return $this;
    }
}
