<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Gcaccount;

use Magento\Backend\App\Action\Context;

/**
 * Delete Account controller
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var resultRedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * For Model account
     *
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $modelAccount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Mageants\GiftCard\Model\Account $modelAccount
     */
    public function __construct(
        Context $context,
        \Mageants\GiftCard\Model\Account $modelAccount
    ) {
        parent::__construct($context);
        $this->modelAccount=$modelAccount;
    }

    /**
     * DeleteAccount controller
     */
    public function execute()
    {
        
        if ($this->getRequest()->getParam('account_id')!=''):
            $id = $this->getRequest()->getParam('account_id');
            $resultRedirect = $this->resultRedirectFactory->create();
            $row = $this->modelAccount->load($id);
            $gift = $row->getGiftCode();
            $row->delete();
            $this->messageManager->addSuccess(__('A total of %1 have been deleted.', $gift));
            $this->_redirect('giftcertificate/gcaccount/');
        endif;
    }
}
