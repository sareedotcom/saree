<?php
namespace Potato\Zendesk\Block\Adminhtml\View;

use Magento\Backend\Block\Template;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;

class CreateTicket extends Template
{
    /** @var string  */
    protected $_template = 'Potato_Zendesk::ticket/form/createTicket.phtml';

    /** @var TicketManagement  */
    protected $ticketManagement;

    /**
     * @param Template\Context $context
     * @param TicketManagement $ticketManagement
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        TicketManagement $ticketManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ticketManagement = $ticketManagement;
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('po_zendesk/ticket/create', ['_current' => true]);
    }
}
