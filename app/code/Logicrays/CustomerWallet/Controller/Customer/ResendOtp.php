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

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

class ResendOtp extends \Magento\Framework\App\Action\Action
{
    /**
     * RedirectFactory variable
     *
     * @var redirectFactory
     */
    protected $resultRedirect;

    /**
     * Session variable
     *
     * @var customerSession
     */
    protected $customerSession;

    /**
     * PageFactory variable
     *
     * @var pageFactory
     */
    protected $pageFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection
     */
    private $otpCollection;

    /**
     * @var \Logicrays\CustomerWallet\Model\VerifyOtpFactory
     */
    private $verifyOtpFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory
     */
    private $walletCollectionFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    private $customerWalletFactory;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    private $sendMail;

    /**
     * __construct function
     *
     * @param RedirectFactory $resultRedirect
     * @param Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection
     * @param \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $walletCollectionFactory
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     */
    public function __construct(
        RedirectFactory $resultRedirect,
        Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection,
        \Logicrays\CustomerWallet\Model\VerifyOtpFactory $verifyOtpFactory,
        \Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet\CollectionFactory $walletCollectionFactory,
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\Mail $sendMail
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
        $this->otpCollection = $otpCollection;
        $this->verifyOtpFactory = $verifyOtpFactory;
        $this->walletCollectionFactory = $walletCollectionFactory;
        $this->customerWalletFactory = $customerWalletFactory;
        $this->helperData = $helperData;
        $this->sendMail = $sendMail;
        return parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return mixed
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirect->create();
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        try {
            $email = $this->customerSession->getCustomer()->getEmail();
            $collection = $this->otpCollection->addFieldToFilter('email', $email);
            foreach ($collection as $otpCollections) {
                $otpId = $otpCollections->getId();
                if ($otpCollections->getIsVerified()) {
                    $this->messageManager->addError(__("You haven't any request to verify OTP"));
                    return $resultRedirect->setPath('*/*/transfer');
                }
                $model = $this->verifyOtpFactory->create()->load($otpId);
                $model->delete();
                $requestId = $otpCollections->getRequestId();
            }

            $resendOtp = $this->setOtp();
            // saving re-send otp data
            $model = $this->verifyOtpFactory->create();
            $otpData = [
                'request_id' => $requestId,
                'email' => $email,
                'otp' => $resendOtp,
            ];
            $model->setData($otpData);
            $model->save();

            $walletCollection = $this->walletCollectionFactory->create()
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('id', $requestId);

            foreach ($walletCollection as $walletCollectionData) {
                $id = $walletCollectionData->getId();
                $model = $this->customerWalletFactory->create();
                $model->load($id);
                $model->setOtp($resendOtp);
                $model->save();
                // set resend data on mail
                $resendData =
                [
                    'otp' => $resendOtp,
                    'name' => $model->getName(),
                    'symbol' => $this->helperData->getCurrentCurrencySymbol()
                ];
            }
            if ($this->helperData->sendEmailEnabled()) {
                $this->sendMail->send(
                    $resendData,
                    $this->helperData->resendMoneyTemplate(),
                    $email,
                );
            }
            $this->messageManager->addSuccess(__("OTP has been Re-send, please check your Email"));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/verifyotp');
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
