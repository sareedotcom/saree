<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Gcaccount;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Mageants\GiftCard\Model\Account;
use \Mageants\GiftCard\Model\Customer;
use \Magento\Backend\Model\Session;

/**
 * AddAccount controller
 */
class Addaccount extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    public $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $sessionId;

    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $modelAccount;
    
    /**
     * @var \Mageants\GiftCard\Model\Customer
     */
    protected $modelCustomer;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Account $modelAccount
     * @param Session $sessionId
     * @param Customer $modelCustomer
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Account $modelAccount,
        Session $sessionId,
        Customer $modelCustomer
    ) {
        parent::__construct($context);
        $this->modelAccount=$modelAccount;
        $this->modelCustomer=$modelCustomer;
        $this->sessionId=$sessionId;
        $this->_coreRegistry = $coreRegistry;
    }
    
    /**
     * Execute method for perform addAccount controller
     */
    public function execute()
    {
        $_sessionId = $this->sessionId;
        $accountid = (int) $this->getRequest()->getParam('account_id');
        $accountData = $this->modelAccount;
        if ($accountid) {
            $accountData = $accountData->load($accountid);
            $templateTitle = $accountData->getGiftCode();
                $customerData = $this->modelCustomer->load($accountData->getOrderId());
                    $accountData['recipient_name']=$customerData->getRecipientName();
                    $accountData['recipient_email']=$customerData->getRecipientEmail();
                    $_sessionId->setOrderId($customerData->getOrderId());
            if (!$accountData->getAccountId()) {
                $this->messageManager->addError(__('Template no longer exist.'));
                $this->_redirect('giftcertificate/gcaccounts/');
                return;
            }
        }
        
        $this->_coreRegistry->register('account_data', $accountData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $accountid ? __('Edit Account ').$templateTitle : __('Add Account');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
