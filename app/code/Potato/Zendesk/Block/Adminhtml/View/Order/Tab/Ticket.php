<?php
namespace Potato\Zendesk\Block\Adminhtml\View\Order\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Potato\Zendesk\Model\Source\ZendeskDate;
use Potato\Zendesk\Model\Source\TicketStatus;
use Magento\Sales\Model\Order;
use Potato\Zendesk\Api\Data\TicketInterface;

class Ticket extends Template implements TabInterface
{
    /** @var string  */
    protected $_template = 'ticket/tab/history.phtml';

    /** @var Registry|null  */
    protected $coreRegistry = null;

    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var null|array  */
    protected $ticketList = null;

    /** @var ZendeskDate  */
    protected $zendeskDate;

    /** @var TicketStatus  */
    protected $ticketStatus;

    /**
     * @param Template\Context $context
     * @param Registry $registry
     * @param TicketManagement $ticketManagement
     * @param ZendeskDate $zendeskDate
     * @param TicketStatus $ticketStatus
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        TicketManagement $ticketManagement,
        ZendeskDate $zendeskDate,
        TicketStatus $ticketStatus,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ticketManagement = $ticketManagement;
        $this->zendeskDate = $zendeskDate;
        $this->ticketStatus = $ticketStatus;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Zendesk Tickets');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Zendesk Tickets');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('po_zendesk/order/ticket', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return array|null|TicketInterface[]
     */
    public function getTicketList()
    {
        if (!$this->getOrder()) {
            return [];
        }
        if (!$this->ticketList) {
            $customerEmail = $this->getOrder()->getCustomerEmail();
            $this->ticketList = $this->ticketManagement->getTicketListByCustomerEmail($customerEmail);
            foreach ($this->ticketList as $key => $ticket) {
                if ($ticket->getOrder() != $this->getOrder()->getIncrementId()) {
                    unset($this->ticketList[$key]);
                }
            }
        }
        return $this->ticketList;
    }

    /**
     * @param string $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        $dateTime = \DateTime::createFromFormat(ZendeskDate::DATA_ZULU_FORMAT, $date);
        return $this->zendeskDate->getFormattedDate($dateTime);
    }

    /**
     * @param string $status
     * @return string
     */
    public function getTicketStatus($status)
    {
        return $this->ticketStatus->getStatusLabel($status);
    }
}
