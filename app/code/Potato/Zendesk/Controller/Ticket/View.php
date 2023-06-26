<?php
namespace Potato\Zendesk\Controller\Ticket;

use Magento\Framework\Controller\ResultFactory;
use Potato\Zendesk\Controller\Ticket as TicketAbstract;
use Magento\Framework\App\Action;
use Magento\Customer\Model\Session as CustomerSession;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;

class View extends TicketAbstract
{
    /** @var TicketManagement */
    protected $ticketManagement;

    /**
     * @param Action\Context $context
     * @param CustomerSession $customerSession
     * @param TicketManagement $ticketManagement
     */
    public function __construct(
        Action\Context $context,
        CustomerSession $customerSession,
        TicketManagement $ticketManagement
    ) {
        parent::__construct($context, $customerSession);
        $this->ticketManagement = $ticketManagement;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ticketList = $this->ticketManagement->getTicketListByCustomerId($this->customerSession->getCustomerId());
        $ticketIdList = [];
        foreach ($ticketList as $ticket) {
            $ticketIdList[] = $ticket->getId();
        }
        $ticketId = $this->getRequest()->getParam('ticket_id', null);
        if (null === $ticketId || !in_array($ticketId, $ticketIdList)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Ticket not found.'));
            return $resultRedirect->setPath('*/*/history');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('po_zendesk/ticket/history');
        }
        return $resultPage;
    }
}
