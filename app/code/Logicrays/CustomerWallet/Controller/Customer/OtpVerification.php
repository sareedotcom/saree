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

class OtpVerification extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * ManagerInterface variable
     *
     * @var messageManager
     */
    protected $messageManager;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection
     */
    private $verifyOtpCollection;

    /**
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    private $customerWalletFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    private $sendMail;

    /**
     * @var \Logicrays\CustomerWallet\Model\VerifyOtpFactory
     */
    private $verifyOtpFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * __construct function
     *
     * @param ManagerInterface $messageManager
     * @param Context $context
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $verifyOtpCollection
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     * @param \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        ManagerInterface $messageManager,
        Context $context,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $verifyOtpCollection,
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Logicrays\CustomerWallet\Model\Mail $sendMail,
        \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helperData = $helperData;
        $this->verifyOtpCollection = $verifyOtpCollection;
        $this->customerWalletFactory = $customerWalletFactory;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->sendMail = $sendMail;
        $this->verifyOtpFactory = $verifyOtpFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Save and verify otp action
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

        $customerEmail = $this->helperData->getSenderEmail();
        $verifyOtp = $this->getRequest()->getParam('verify-otp');

        $collection = $this->verifyOtpCollection
        ->addFieldToFilter('email', $customerEmail)
        ->addFieldToFilter('otp', $verifyOtp);
        $otpCollection = $collection->getData();

        if (empty($otpCollection)) {
            $this->messageManager->addError(__("Invalid OTP."));
            return $resultPageFactory->setPath('*/*/verifyotp');
        } else {
            foreach ($otpCollection as $otpCollections) {
                $createdAt = $otpCollections['created_at'];
                $otp = $otpCollections['otp'];
                $otpvalidity = $this->helperData->getOtpValidity();
                if (empty($otpvalidity)) {
                    $otpvalidity = '3';
                }
                $validity = strtotime($createdAt.'+ '.$otpvalidity.' minute');
                $extendedValidity = date('Y-m-d H:i:s', $validity);
                $currDate = date('Y-m-d H:i:s');
                if ($currDate>$extendedValidity) {
                    $this->messageManager->addError(__("OTP is expired, please re-send."));
                    return $resultPageFactory->setPath('*/*/verifyotp');
                }
                $requestId = $otpCollections['request_id'];
                $verifyotpId = $otpCollections['id'];
            }
            $receiverData = $this->setReceiverData($requestId);

            try {
                // updating OTP is verified in wallet request
                $sendersUpdate = $this->customerWalletFactory->create();
                $sendersUpdate->load($receiverData['sender_id']);
                if ($sendersUpdate->getOtp() != $otp) {
                    $this->messageManager->addError(__("Invalid OTP."));
                    return $resultPageFactory->setPath('*/*/verifyotp');
                }
                $sendersUpdate->setStatus(4);
                $sendersUpdate->setIsVerified(1);
                $sendersUpdate->save();

                // updating OTP is verified in OTP table
                $verifyOtp = $this->verifyOtpFactory->create();
                $verifyOtp->load($verifyotpId);
                $verifyOtp->setIsVerified(1);
                $verifyOtp->save();

                $model = $this->customerWalletFactory->create();
                $model->setData($receiverData);
                $model->save();
                if ($this->helperData->sendEmailEnabled()) {
                    $this->sendMail->send(
                        $receiverData,
                        $this->helperData->receiverMoneyTemplate(),
                        $receiverData['email'],
                    );
                }
                $this->messageManager->addSuccess(__("Money Send Successfully to ".$model->getEmail()));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        return $resultPageFactory->setPath('*/*/transfer');
    }

    /**
     * Set Receiver Data function
     *
     * @param int $requestId
     * @return array
     */
    public function setReceiverData($requestId)
    {
        $requestData = $this->customerWalletFactory->create()->load($requestId, 'id');
        $websiteID = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()
            ->setWebsiteId($websiteID)
            ->loadByEmail($requestData->getPayeeEmail());

        $receiverData =
        [
            'sender_id' => $requestData->getId(),
            'name' => $customer->getName(),
            'email' => $requestData->getPayeeEmail(),
            'customer_id' => $customer->getId(),
            'payee_email' => '',
            'amount' => $requestData->getAmount(),
            'note' => 'Received from '.$requestData->getEmail(),
            'status' => 1,
            'symbol' => $this->helperData->getCurrentCurrencySymbol()
        ];
        return $receiverData;
    }
}
