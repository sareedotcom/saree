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

use Magento\Framework\Event\ObserverInterface;

class DisablePaymentMethod implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cartItems;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Checkout\Model\Cart $cartItems
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cartItems,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->cartItems = $cartItems;
        $this->helperData = $helperData;
    }

    /**
     * Execute function when addmoney request
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->cartItems->getQuote();
        $methodCode = $observer->getEvent()->getMethodInstance()->getCode();
        $checkResult = $observer->getEvent()->getResult();

        if (!empty($this->helperData->disablePaymentMethod())) {
            $disablePaymentMethods[0] = $this->helperData->disablePaymentMethod();
            $disablePaymentMethods = explode(",", $disablePaymentMethods[0]);
            $allItems = $this->cartItems->getQuote()->getAllItems();
            foreach ($allItems as $item) {
                if ($item->getSku() == $this->helperData->walletSKU()) {
                    if (in_array($methodCode, $disablePaymentMethods)) {
                        $checkResult->setData('is_available', false);
                        //this is disabling the payment method at checkout page
                    }
                }
            }
        }
        
        if ($quote->getWalletamount() > '0') {
            if ($quote->getGrandTotal() <= '0' && $quote->getWalletamount() == $quote->getBaseGrandTotal()) {
                if (!empty($this->helperData->disablePaymentMethodWalletUsed())) {
                    $disableMethodWalletUsed[0] = $this->helperData->disablePaymentMethodWalletUsed();
                    $disableMethodWalletUsed = explode(",", $disableMethodWalletUsed[0]);
                    if (in_array($methodCode, $disableMethodWalletUsed)) {
                        $checkResult->setData('is_available', false);
                    }
                }
            }
        }
        return $this;
    }
}
