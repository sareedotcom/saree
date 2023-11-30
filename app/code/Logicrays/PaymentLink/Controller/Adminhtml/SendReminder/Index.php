<?php

namespace Logicrays\PaymentLink\Controller\Adminhtml\SendReminder;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Logicrays\PaymentLink\Helper\Email;
use Magento\Framework\Message\ManagerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @param Context $context
     * @param Email $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Email $helper,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->helper = $helper;
        $this->_messageManager = $messageManager;
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        try{
            $data = $this->context->getRequest()->getParams();
            $this->helper->sendEmail($data);

            $this->_messageManager->addSuccessMessage('Payment link is succssfully send to customer');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($data['currentUrl']);
        }catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        return $resultRedirect;
    }
}