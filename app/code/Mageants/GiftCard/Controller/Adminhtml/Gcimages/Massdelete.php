<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mageants\GiftCard\Model\ResourceModel\Templates\CollectionFactory;
use \Mageants\GiftCard\Model\Templates;

/**
 * MassDelete Image Template
 */
class Massdelete extends \Magento\Backend\App\Action
{
    /**
     * @var Mageants\GiftCard\Model\Templates
     */
    public $template;
    /**
     * @var Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;
    
    /**
     * @var Mageants\GiftCard\Model\ResourceModel\Templates\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Templates $template
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Templates $template,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->template = $template;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
 
    /**
     * Execute MassDelete for Template
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection->getData() as $items) {
            $id=$items['image_id'];
            $row = $this->template->load($id);
            $row->delete();
        }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
 
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
