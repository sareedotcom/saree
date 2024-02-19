<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mageants\GiftCard\Model\ResourceModel\Codeset\CollectionFactory;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Codeset;

/**
 * Perform MassDelete controller Action
 */
class Massdelete extends \Magento\Backend\App\Action
{
    /**
     * @var Codeset
     */
    public $codeset;

    /**
     * @var Codelist
     */
    public $_codelist;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * @var \Mageants\GiftCard\Model\ResourceModel\Codeset\CollectionFactory
     */
    protected $_collectionFactory;
    
    /**
     * @param Context $context
     * @param Codelist $_codelist
     * @param Codeset $codeset
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Codelist $_codelist,
        Codeset $codeset,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_codelist = $_codelist;
        $this->codeset = $codeset;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    
    /**
     * Perform MassDelete Action
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection->getData() as $items) {
            $id=$items['code_set_id'];
            $row = $this->codeset->load($id);
            $codelist = $this->_codelist->getCollection()
            ->addFieldToFilter('code_set_id', $id);
            $row->delete();
            foreach ($codelist as $list) {
                $list->delete();
            }
        }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
 
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
