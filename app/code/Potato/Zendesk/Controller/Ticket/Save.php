<?php

namespace Potato\Zendesk\Controller\Ticket;

use Magento\Framework\App\Action;
use Potato\Zendesk\Model\Authorization;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Magento\Customer\Model\Session;
use Potato\Zendesk\Controller\Ticket as TicketAbstract;
use Psr\Log\LoggerInterface;

class Save extends TicketAbstract
{
    /** @var Authorization  */
    protected $authorization;

    /** @var  StoreManagerInterface */
    protected $storeManager;
    
    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param Session $customerSession
     * @param Authorization $authorization
     * @param StoreManagerInterface $storeManager
     * @param TicketManagement $ticketManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        Session $customerSession,
        Authorization $authorization,
        StoreManagerInterface $storeManager,
        TicketManagement $ticketManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $customerSession);
        $this->authorization = $authorization;
        $this->storeManager = $storeManager;
        $this->ticketManagement = $ticketManagement;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $attachments = [];
        if (!empty($_FILES) && array_key_exists('file', $_FILES)) {
            $attachments = $_FILES["file"];
        }
        if (!$postData) {
            $this->messageManager->addErrorMessage(__('Data not found.'));
            return $resultRedirect->setPath('*/*/history');
        }
        $store = $this->storeManager->getStore();
        
        try {
            $this->ticketManagement->updateTicket($postData, $store, $attachments);
            $this->messageManager->addSuccessMessage(__('The ticket was been successfully updated.'));
        } catch (\Exception $e) {
            $this->getSession()->setTicketFormData($postData);
            $this->logger->critical($e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the ticket.'));
        }
        return $resultRedirect->setPath('*/*/view', ['ticket_id' => $postData['ticket_id']]);
    }
}
