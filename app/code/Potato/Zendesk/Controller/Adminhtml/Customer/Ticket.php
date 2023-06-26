<?php
namespace Potato\Zendesk\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;
use Potato\Zendesk\Block\Adminhtml\View\Customer\Tab\Ticket as TicketTab;

class Ticket extends Index
{
    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $layout = $this->layoutFactory->create();
        $html = $layout->createBlock(TicketTab::class)
            ->getProcessedResponseBody();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($html);
        return $resultRaw;
    }
}
