<?php
namespace Logicrays\NewDashboard\Controller\Adminhtml\Order;

class Index extends \Magento\Backend\App\Action
{
        protected $resultPageFactory = false;
        public function __construct(
                \Magento\Backend\App\Action\Context $context,
                \Magento\Framework\View\Result\PageFactory $resultPageFactory
        ) {
                parent::__construct($context);
                $this->resultPageFactory = $resultPageFactory;
        }
        public function execute()
        {
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Logicrays_VendorManagement::grid');
                $resultPage->getConfig()->getTitle()->prepend(__('LogicRays Orders'));
                return $resultPage;
        }

        /**
         * Check Grid List Permission.
         *
         * @return bool
         */
        protected function _isAllowed()
        {
                return $this->_authorization->isAllowed('Logicrays_NewDashboard::ordergrid');
        }

}