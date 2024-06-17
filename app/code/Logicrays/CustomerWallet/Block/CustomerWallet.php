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

namespace Logicrays\CustomerWallet\Block;

class CustomerWallet extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $collectionFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Get Form Action function
     *
     * @return string
     */
    public function getPayee()
    {
        return $this->getUrl('wallet/customer/transfer', ['_secure' => true]);
    }

    /**
     * Add Money Action function
     *
     * @return string
     */
    public function addMoney()
    {
        return $this->getUrl('wallet/customer/index', ['_secure' => true]);
    }

    /**
     * Send Amount To Payee function
     *
     * @return string
     */
    public function sendAmountToFriend()
    {
        return $this->getUrl('wallet/customer/sendmoney', ['_secure' => true]);
    }

    /**
     * Verify OTP function
     *
     * @return string
     */
    public function verifyOtp()
    {
        return $this->getUrl('wallet/customer/otpverification', ['_secure' => true]);
    }

    /**
     * Re-send OTP function
     *
     * @return string
     */
    public function resendOtp()
    {
        return $this->getUrl('wallet/customer/resendotp', ['_secure' => true]);
    }

    /**
     * Get Wallet SKU function
     *
     * @return string
     */
    public function getWalletSKU()
    {
        return $this->helperData->walletSKU();
    }

    /**
     * Get Customer Wallet Amount function
     *
     * @return string
     */
    public function getWalletAmount()
    {
        return $this->helperData->getRemainWalletAmount();
    }

    /**
     * Get Sender Email function
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->helperData->getSenderEmail();
    }

    /**
     * Get Sender ID function
     *
     * @return string
     */
    public function getSenderId()
    {
        return $this->helperData->getSenderId();
    }

    /**
     * Get Sender Name function
     *
     * @return string
     */
    public function getSenderName()
    {
        return $this->helperData->getSenderName();
    }

    /**
     * Get Currency Symbol function
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->helperData->getCurrentCurrencySymbol();
    }

    /**
     * GetWalletBalanceText function
     *
     * @return string
     */
    public function getWalletBalanceText()
    {
        return $this->helperData->getWalletBalanceText();
    }

    /**
     * GetText SEnd money to friends function
     *
     * @return string
     */
    public function getTextSendMoneyFriends()
    {
        return $this->helperData->getTextSendMoneyFriends();
    }
}
