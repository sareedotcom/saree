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

namespace Logicrays\CustomerWallet\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;

class WalletPayment implements ConfigProviderInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
    }

    /**
     * Get Config for checkout window function
     *
     * @return array
     */
    public function getConfig()
    {
        $email = $this->customerSession->getCustomer()->getEmail();
        $additionalVariables['remain_wallet_amount'] = $this->helperData->getRemainWalletAmount();

        $additionalVariables['store_base_currency_symbol'] = $this->helperData->getCurrentCurrencySymbol();

        $quote = $this->checkoutSession->getQuote();
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();

        // set to don't show wallet on checkout when it's add money request
        foreach ($quoteItems as $quoteItem) {
            $disableWallet = 0;
            if ($quoteItem->getSku() == $this->helperData->walletSKU()) {
                $disableWallet = 1;
            }
        }
        $additionalVariables['checkout_sku'] = $quoteItem->getSku();
        $additionalVariables['wallet_sku'] = $this->helperData->walletSKU();
        $additionalVariables['disable_wallet'] = $disableWallet;
        $walletappliedAmount = $quote->getWalletamount();
        $additionalVariables['wallet_applied_amount'] = $walletappliedAmount;
        $isEnabled = 0;
        if ($this->helperData->isEnabled()) {
            $isEnabled = 1;
        }
        $additionalVariables['wallet_module_is_enable'] = $isEnabled;
        $isLoggedIn = 0;
        if ($email) {
            $isLoggedIn = 1;
        }
        $additionalVariables['is_logged_in'] = $isLoggedIn;
        return $additionalVariables;
    }
}
