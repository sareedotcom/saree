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

namespace Logicrays\CustomerWallet\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;

class Data extends AbstractHelper
{
    public const MODULE_IS_ENABLED = 'customerwallet/general/enabled';

    public const REFUND_IN_WALLET = 'customerwallet/general/refund_in_wallet';

    public const DISABLE_PAYMENT_METHOD = 'customerwallet/general/disable_paymentmethods';

    public const DISABLE_METHOD_WALLET_USED = 'customerwallet/general/disable_methods';

    public const WALLET_SKU = 'customerwallet/general/wallet_sku';

    public const ENABLE_AUTO_INVOICE = 'customerwallet/general/enable_auto_invoice';

    public const AUTO_INVOICE_ORDER_COMMENT = 'customerwallet/general/auto_invoice_order_comment';

    public const OTP_VALIDITY = 'customerwallet/general/otp_validity';

    public const MAX_LIMIT = 'customerwallet/general/max_limit';

    public const MAX_LIMIT_MSG = 'customerwallet/general/max_limit_msg';

    public const WALLETBALANCETEXT = 'customerwallet/general/walletbalancetext';

    public const TEXTSENDMONEYFRIENDS = 'customerwallet/general/textsendmoneyfriends';

    public const TEXTADDEDMONEYTOWALLET = 'customerwallet/general/textaddmoneytowallet';

    public const MONEY_IN_CART = 'customerwallet/general/money_in_cart';

    public const OTHER_PRODUCTS_IN_CART = 'customerwallet/general/other_products_in_cart';

    public const XML_PATH_SEND_EMAIL_ENABLED = 'customerwallet/email/sendemail';

    public const XML_PATH_GET_SENDER = 'customerwallet/email/sender_email';

    public const SEND_ADMIN_MAIL_ENABLED = 'customerwallet/email/send_admin_mail';

    public const XML_PATH_ADMIN_EMAIL = 'customerwallet/email/admin_email';

    public const ADMIN_EMAIL_TEMPLATE = 'customerwallet/email/admintemplate';

    public const CUSTOMER_EMAIL_TEMPLATE = 'customerwallet/email/customertemplate';

    public const SENDER_MONEY_TO_FRIEND_TEMPLATE = 'customerwallet/email/sendmoney';

    public const RECEIVER_MONEY_TEMPLATE = 'customerwallet/email/receivemoney';

    public const RESEND_TOP_EMAIL_TEMPLATE = 'customerwallet/email/resendotp';

    public const ADMIN_ADJUST_EMAIL_TEMPLATE = 'customerwallet/email/adminadjust';

    /**
     * ScopeConfig variable
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * StoreManager variable
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

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
    protected $checkoutSession;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * __construct function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->authSession = $authSession;
        $this->currencyFactory = $currencyFactory;
        $this->orderFactory = $orderFactory;
    }

    /**
     * IsEnabled function
     *
     * @return bool
     */
    public function isEnabled()
    {
        $isEnabled = $this->scopeConfig->isSetFlag(
            self::MODULE_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $isEnabled;
    }

    /**
     * Refund In Wallet function
     *
     * @return bool
     */
    public function refundInWallet()
    {
        $refundInWallet = $this->scopeConfig->isSetFlag(
            self::REFUND_IN_WALLET,
            ScopeInterface::SCOPE_STORE
        );
        return $refundInWallet;
    }

    /**
     * Disable PaymentMethod function
     *
     * @return array
     */
    public function disablePaymentMethod()
    {
        $disablePaymentMethod = $this->scopeConfig->getValue(
            self::DISABLE_PAYMENT_METHOD,
            ScopeInterface::SCOPE_STORE
        );
        return $disablePaymentMethod;
    }

    /**
     * Disable Payment Method when wallet used & order total would be zero
     *
     * @return array
     */
    public function disablePaymentMethodWalletUsed()
    {
        $disablePaymentMethod = $this->scopeConfig->getValue(
            self::DISABLE_METHOD_WALLET_USED,
            ScopeInterface::SCOPE_STORE
        );
        return $disablePaymentMethod;
    }

    /**
     * EnableAutoInvoice function
     *
     * @return string
     */
    public function enableAutoInvoice()
    {
        $enableAutoInvoice = $this->scopeConfig->getValue(
            self::ENABLE_AUTO_INVOICE,
            ScopeInterface::SCOPE_STORE
        );
        return $enableAutoInvoice;
    }

    /**
     * AutoInvoice Order Comment function
     *
     * @return string
     */
    public function autoInvoiceOrderComment()
    {
        $autoInvoiceOrderComment = $this->scopeConfig->getValue(
            self::AUTO_INVOICE_ORDER_COMMENT,
            ScopeInterface::SCOPE_STORE
        );
        return $autoInvoiceOrderComment;
    }

    /**
     * Wallet SKU function
     *
     * @return string
     */
    public function walletSKU()
    {
        $walletSKU = $this->scopeConfig->getValue(
            self::WALLET_SKU,
            ScopeInterface::SCOPE_STORE
        );
        return $walletSKU;
    }

    /**
     * OTP Validity function
     *
     * @return string
     */
    public function getOtpValidity()
    {
        $getOtpValidity = $this->scopeConfig->getValue(
            self::OTP_VALIDITY,
            ScopeInterface::SCOPE_STORE
        );
        return $getOtpValidity;
    }

    /**
     * Max Wallet Limit to add Money function
     *
     * @return string
     */
    public function getMaxLimit()
    {
        $getMaxLimit = $this->scopeConfig->getValue(
            self::MAX_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
        return $getMaxLimit;
    }

    /**
     * Max Wallet Limit to add Money function
     *
     * @return string
     */
    public function getMaxLimitMsg()
    {
        $getMaxLimitMsg = $this->scopeConfig->getValue(
            self::MAX_LIMIT_MSG,
            ScopeInterface::SCOPE_STORE
        );
        return $getMaxLimitMsg;
    }

    /**
     * WalletBalance Text function
     *
     * @return string
     */
    public function getWalletBalanceText()
    {
        $WalletBalanceText = $this->scopeConfig->getValue(
            self::WALLETBALANCETEXT,
            ScopeInterface::SCOPE_STORE
        );
        return $WalletBalanceText;
    }

    /**
     * Text to send Money to Friends function
     *
     * @return string
     */
    public function getTextSendMoneyFriends()
    {
        $textSendMoneyFriends = $this->scopeConfig->getValue(
            self::TEXTSENDMONEYFRIENDS,
            ScopeInterface::SCOPE_STORE
        );
        return $textSendMoneyFriends;
    }

    /**
     * Text to send Money to Friends function
     *
     * @return string
     */
    public function getTextAddedMoneyToWallet()
    {
        $textAddedMoneyToWallet = $this->scopeConfig->getValue(
            self::TEXTADDEDMONEYTOWALLET,
            ScopeInterface::SCOPE_STORE
        );
        return $textAddedMoneyToWallet;
    }

    /**
     * Money In Cart function
     *
     * @return string
     */
    public function moneyInCart()
    {
        $moneyInCart = $this->scopeConfig->getValue(
            self::MONEY_IN_CART,
            ScopeInterface::SCOPE_STORE
        );
        return $moneyInCart;
    }

    /**
     * Other Products In Cart function
     *
     * @return string
     */
    public function otherProductsInCart()
    {
        $otherProductsInCart = $this->scopeConfig->getValue(
            self::OTHER_PRODUCTS_IN_CART,
            ScopeInterface::SCOPE_STORE
        );
        return $otherProductsInCart;
    }

    /**
     * SendEmailEnabled function
     *
     * @return bool
     */
    public function sendEmailEnabled()
    {
        $sendEmailEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_EMAIL_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $sendEmailEnabled;
    }

    /**
     * Get Sender Email function
     *
     * @return array
     */
    public function senderEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GET_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * SendAdminMailEnabled function
     *
     * @return bool
     */
    public function sendAdminMailEnabled()
    {
        $sendAdminMailEnabled = $this->scopeConfig->isSetFlag(
            self::SEND_ADMIN_MAIL_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
        return $sendAdminMailEnabled;
    }

    /**
     * AdminEmailTemplate function
     *
     * @return string
     */
    public function adminEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * CustomerEmailTemplate function
     *
     * @return string
     */
    public function customerEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::CUSTOMER_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * SenderMoneyTemplate function
     *
     * @return string
     */
    public function senderMoneyTemplate()
    {
        return $this->scopeConfig->getValue(
            self::SENDER_MONEY_TO_FRIEND_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * ReceiverMoneyTemplate function
     *
     * @return string
     */
    public function receiverMoneyTemplate()
    {
        return $this->scopeConfig->getValue(
            self::RECEIVER_MONEY_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Admin Adjust Email Template function
     *
     * @return string
     */
    public function adminAdjustEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_ADJUST_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Resend OTP Email Template function
     *
     * @return string
     */
    public function resendMoneyTemplate()
    {
        return $this->scopeConfig->getValue(
            self::RESEND_TOP_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * AdminEmail function
     *
     * @return string
     */
    public function adminEmail()
    {
        $adminEmail = $this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
        return $adminEmail;
    }

    /**
     * SetTemplateOptions function
     *
     * @return array
     */
    public function setTemplateOptions()
    {
        $storeId = $this->storeManager->getStore()->getId();

        return $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $storeId
        ];
    }

    /**
     * Calculate and return total custom discount from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return int|float
     */
    public function getTotalWalletAmountFromOrder($order)
    {
        $totalWalletamount = 0;
        if ($order->getWalletamount()) {
            $totalWalletamount = (float)$order->getWalletamount();
            $totalWalletamount = abs($totalWalletamount);
        }
        $rate = $this->storeManager->getStore()->getBaseCurrency()->getRate($order->getOrderCurrencyCode());
        $convertedRateAmount = $totalWalletamount * $rate;
        // return "-".$totalWalletamount;
        return "-".$convertedRateAmount;
    }

    /**
     * Get Current Currency Symbol function
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $baseCurrencyCode = $this->storeManager->getStore($storeId)->getBaseCurrencyCode();
        $currency = $this->currencyFactory->create()->load($baseCurrencyCode);

        return $currency->getCurrencySymbol();
    }

    /**
     * Get Base Currency code function
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $baseCurrencyCode = $this->storeManager->getStore($storeId)->getBaseCurrencyCode();
        return $baseCurrencyCode;
    }

    /**
     * GetCurrentStoreCurrency function
     *
     * @return string
     */
    public function getCurrentStoreCurrency()
    {
        $currency = $this->storeManager->getStore()->getCurrentCurrency();
        return $currency->getCurrencyCode();
    }

    /**
     * Get Customer Wallet Amount function
     *
     * @return string
     */
    public function getRemainWalletAmount()
    {
        $walletAmount = $this->getCreditedAmount() - $this->getDebitedAmount();
        if ($walletAmount <= 0) {
            return 0.00;
        }
        return $walletAmount;
    }

    /**
     * Get Credited Wallet Amount function
     *
     * @return float
     */
    public function getCreditedAmount()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $creditedAmountData = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', '1');
        $creditedAmount = 0;
        foreach ($creditedAmountData as $amount) {
            $creditedAmount += $amount->getAmount();
        }
        return $creditedAmount;
    }

    /**
     * Get Debited Wallet Amount function
     *
     * @return float
     */
    public function getDebitedAmount()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $debitedAmountData = $this->collectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', '4');
        $debitedAmount = 0;
        foreach ($debitedAmountData as $amount) {
            $debitedAmount += $amount->getAmount();
        }
        return $debitedAmount;
    }

    /**
     * Get Sender Email function
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->customerSession->getCustomer()->getEmail();
    }

    /**
     * Get Sender Id function
     *
     * @return string
     */
    public function getSenderId()
    {
        return $this->customerSession->getCustomer()->getId();
    }

    /**
     * Get Sender Name function
     *
     * @return string
     */
    public function getSenderName()
    {
        return $this->customerSession->getCustomer()->getName();
    }

    /**
     * Get Checkout Product Sku function
     *
     * @return string
     */
    public function getCheckoutProductSku()
    {
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            $productSku = 0;
            if ($quoteItem->getSku() == $this->walletSKU()) {
                $productSku = $quoteItem->getSku();
            }
        }
        return $productSku;
    }

    /**
     * Get Current Admin User function
     *
     * @return string
     */
    public function getAdminUser()
    {
        $user = $this->authSession->getUser();
        return $user->getFirstname();
    }

    /**
     * GetOrderId function
     *
     * @param int $orderIncrementId
     * @return string
     */
    public function getOrderId($orderIncrementId)
    {
        $orderId = '';
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        if ($order->getId()) {
            $orderId = $order->getId();
        }
        return $orderId;
    }
}
