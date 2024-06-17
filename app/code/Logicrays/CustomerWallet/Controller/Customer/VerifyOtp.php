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

class VerifyOtp extends \Magento\Framework\App\Action\Action
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
     * __construct function
     *
     * @param RedirectFactory $resultRedirect
     * @param Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection
     */
    public function __construct(
        RedirectFactory $resultRedirect,
        Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp\Collection $otpCollection
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
        $this->otpCollection = $otpCollection;
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
        $email = $this->customerSession->getCustomer()->getEmail();
        $collection = $this->otpCollection->addFieldToFilter('email', $email);
        foreach ($collection as $otpCollections) {
            if ($otpCollections->getIsVerified()) {
                return $resultRedirect->setPath('*/*/transfer');
            }
        }
        return $this->pageFactory->create();
    }
}
