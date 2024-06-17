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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RestrictAddToCart implements ObserverInterface
{
    /**
     * ManagerInterface variable
     *
     * @var messageManager
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartItems;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Cart $cartItems
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cartItems,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->messageManager = $messageManager;
        $this->cartItems = $cartItems;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
    }

    /**
     * Execute to restrict add to cart function
     *
     * @param Observer $observer
     * @return mixed
     */
    public function execute(Observer $observer)
    {
        $url = $this->storeManager->getStore()->getBaseUrl().'checkout/cart/';
        $allItems = $this->cartItems->getQuote()->getAllItems();
        $msg = $this->helperData->moneyInCart();
        if (!$msg) {
            $msg = 'You have Money In Cart, Proceed to Pay OR Clear it';
        }
        foreach ($allItems as $item) {
            if ($item->getSku() == $this->helperData->walletSKU()) {
                $this->messageManager->addNotice(__("$msg <a href='$url'>shopping cart</a>"));
                //set false if you not want to add product to cart
                $observer->getRequest()->setParam('product', false);
                return $this;
            }
        }
        return $this;
    }
}
