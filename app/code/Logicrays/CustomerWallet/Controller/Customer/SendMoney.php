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

namespace Logicrays\CustomerWallet\Controller\Customer;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Logicrays\CustomerWallet\Model\CustomerWalletFactory;

class SendMoney extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * ManagerInterface variable
     *
     * @var messageManager
     */
    protected $messageManager;

    /**
     * CustomerWalletFactory variable
     *
     * @var CustomerWalletFactory
     */
    protected $customerWalletFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    private $sendMail;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\VerifyOtpFactory
     */
    private $verifyOtpFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection
     */
    private $otpCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * __construct function
     *
     * @param ManagerInterface $messageManager
     * @param CustomerWalletFactory $customerWalletFactory
     * @param Context $context
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        ManagerInterface $messageManager,
        CustomerWalletFactory $customerWalletFactory,
        Context $context,
        \Logicrays\CustomerWallet\Model\Mail $sendMail,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory,
        \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->sendMail = $sendMail;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->verifyOtpFactory = $verifyOtpFactory;
        $this->otpCollection = $otpCollection;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->customerWalletFactory = $customerWalletFactory;
        parent::__construct($context);
    }

    /**
     * Save CustomerWallet action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultPageFactory = $this->resultRedirectFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            $resultPageFactory->setPath('customer/account/login');
            return $resultPageFactory;
        }
        $payeeData = $this->getRequest()->getParams();

        // deleting old otp of perticular customer
        $collection = $this->otpCollection->addFieldToFilter('email', $payeeData['sender_email']);
        foreach ($collection as $otpCollections) {
            $otpId = $otpCollections->getId();
            $model = $this->verifyOtpFactory->create()->load($otpId);
            $model->delete();
        }

        if ($payeeData['payee_amount'] > $payeeData['wallet_amount']) {
            $this->messageManager->addError(__("You don't have enough balance, to send ".
            $this->helperData->getCurrentCurrencySymbol().$payeeData['payee_amount']));
            return $resultPageFactory->setPath('*/*/transfer');
        }

        if ($payeeData['sender_email'] == $payeeData['payee_email']) {
            $this->messageManager->addError(__($payeeData['payee_email'].
            " is your email, please use another."));
            return $resultPageFactory->setPath('*/*/transfer');
        }

        // checking is customer or not
        $websiteID = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()
            ->setWebsiteId($websiteID)
            ->loadByEmail($payeeData['payee_email']);
        if (empty($customer->getData())) {
            $this->messageManager->addError(__($payeeData['payee_email'].
            " is not Registered Customer."));
            return $resultPageFactory->setPath('*/*/transfer');
        }

        try {
            $finalOtp = $this->setOtp();
            $senderData = $this->getSenderData();
            $senderData['otp'] = $finalOtp;

            $model = $this->customerWalletFactory->create();
            $model->setData($senderData);
            $model->save();
            $requestId = $model->getId();

            // saving otp data
            $model = $this->verifyOtpFactory->create();
            $otpData = [
                'request_id' => $requestId,
                'email' => $payeeData['sender_email'],
                'otp' => $finalOtp,
            ];
            $model->setData($otpData);
            $model->save();

            if ($this->helperData->sendEmailEnabled()) {
                $this->sendMail->send(
                    $senderData,
                    $this->helperData->senderMoneyTemplate(),
                    $senderData['email'],
                );
                $this->messageManager->addSuccess(__("Please check your email and verify OTP, Send on "
                .$payeeData['sender_email']));
            }

        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultPageFactory->setPath('*/*/verifyotp');
    }

    /**
     * Get Sender Data function
     *
     * @return array
     */
    public function getSenderData()
    {
        $payeeData = $this->getRequest()->getParams();
        $note = $payeeData['payee_note'].' :- to '.$payeeData['payee_email'];
        if (empty($payeeData['payee_note'])) {
            $note = 'Send to '.$payeeData['payee_email'];
        }
        $senderData =
            [
                'name'  => $payeeData['sender_name'],
                'email'  => $payeeData['sender_email'],
                'customer_id'  => $payeeData['customer_id'],
                'payee_email'  => $payeeData['payee_email'],
                'amount'  => $payeeData['payee_amount'],
                'note' => $note,
                'transfer_wallet' => 1,
                'status' => 0,
                'symbol' => $this->helperData->getCurrentCurrencySymbol()
        ];
        return $senderData;
    }

    /**
     * Set OTP function
     *
     * @return int
     */
    public function setOtp()
    {
        return rand(1111, 9999);
    }
}
