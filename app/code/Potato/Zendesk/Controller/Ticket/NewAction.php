<?php
namespace Potato\Zendesk\Controller\Ticket;

use Magento\Framework\Controller\ResultFactory;
use Potato\Zendesk\Controller\Ticket as TicketAbstract;

class NewAction extends TicketAbstract
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
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