<?php
namespace Logicrays\OrderCancellation\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
   /**
    *
    * @var PageFactory
    */
    protected $_pageFactory;

   /**
    *
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

   /**
    *
    * Creates page
    */
    public function execute()
    {
        return $this->_pageFactory->create();
    }
}
