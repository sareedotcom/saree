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

namespace Logicrays\CustomerWallet\Controller\Adminhtml\AdjustAmount;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    private $customerWalletFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    private $adjustAmountMail;

    /**
     * __construct function
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\Mail $adjustAmountMail
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\Mail $adjustAmountMail
    ) {
        $this->_pageFactory = $pageFactory;
        $this->customerWalletFactory = $customerWalletFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->adjustAmountMail = $adjustAmountMail;
        return parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPageFactory = $this->resultRedirectFactory->create();
        // verify is registered customer or not
        $email = $this->getRequest()->getParam('customer_wallet');
        $websiteID = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()
            ->setWebsiteId($websiteID)
            ->loadByEmail($email['email']);
        if (empty($customer->getData())) {
            $this->messageManager->addError(__($email['email'].
            " is not Registered Customer."));
            return $resultPageFactory->setPath('*/*/index');
        }
        try {
            if ($this->setAdjustedData()) {
                $data = $this->getRequest()->getPostValue();
                $model = $this->customerWalletFactory->create();
                $model->setData($this->setAdjustedData());
                $model->setStatus($data['adjust_amount']['status']);
                $model->save();
                if ($this->helperData->sendEmailEnabled() == 1) {
                    $this->adjustAmountMail->send(
                        $this->setAdjustedData(),
                        $this->helperData->adminAdjustEmailTemplate(),
                        $this->setAdjustedData()['email'],
                    );
                }
                $id = $model->getId();
                $this->messageManager->addSuccessMessage(__("Adjust Amount has been submited, Request id ".$id));
                return $resultPageFactory->setPath('*/*/index');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __("We can't submit your request, Please try again."));
        }
    }

    /**
     * Set Adjusted Data function
     *
     * @return array
     */
    public function setAdjustedData()
    {
        $postData = $this->getRequest()->getPostValue();
        $customer = $this->customerFactory->create()
            ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
            ->loadByEmail($postData['customer_wallet']['email']);

        if ($postData['adjust_amount']['status'] == 1) {
            $status = 'Credited';
        } elseif ($postData['adjust_amount']['status'] == 4) {
            $status = 'Debited';
        }

        $note = $postData['adjust_amount']['note'];
        if (empty($postData['adjust_amount']['note'])) {
            $note = 'Your Wallet Adjust by';
        }
        $data =
        [
            'name' => $customer->getName(),
            'email' => $postData['customer_wallet']['email'],
            'customer_id' => $customer->getId(),
            'payee_email' => '',
            'amount' => $postData['adjust_amount']['amount'],
            'note' => $note.' : '.$this->helperData->getAdminUser(),
            'status' => $status
        ];
        return $data;
    }

    /**
     * Is the user allowed to view the page.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Logicrays_CustomerWallet::adjustamount_save');
    }
}
