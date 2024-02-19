<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use \Magento\Backend\App\Action\Context;

/**
 * CodeList for code
 */
class Codelist extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory
    ) {
         $this->_resultPageFactory = $resultPageFactory;
         $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context);
    }

    /**
     * Perform CodeList Action
     */
    public function execute()
    {
         $resultPage = $this->_resultPageFactory->create();
         $result = $resultPage->getLayout()->renderElement('content');
        return $this->resultRawFactory->create()->setContents($result);
    }
}
