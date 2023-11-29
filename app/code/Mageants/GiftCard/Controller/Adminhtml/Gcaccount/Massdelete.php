<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcaccount;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mageants\GiftCard\Model\ResourceModel\Account\CollectionFactory;
use \Mageants\GiftCard\Model\Account;

/*
 * Account MassDelete Controller
 */
class Massdelete extends \Magento\Backend\App\Action
{
    /**
     * For Filter Data
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * For Account Collection
     *
     * @var Mageants\GiftCard\Model\ResourceModel\Account\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @var \Mageants\GiftCertificate\Model\Account
     */
    protected $modelAccount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Filter $filter
     * @param Account $modelAccount
     * @param Mageants\GiftCard\Model\ResourceModel\Account\CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Account $modelAccount,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->modelAccount = $modelAccount;
        parent::__construct($context);
    }
 
    /**
     * Execute method for perform MassDelete controller
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();
       
        foreach ($collection->getData() as $items) {
            $id=$items['account_id'];
            $row = $this->modelAccount->load($id);
            $row->delete();
        }
         $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
