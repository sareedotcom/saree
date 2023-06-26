<?php
namespace Potato\Zendesk\Block\Ticket;

use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Potato\Zendesk\Model\Source\ZendeskDate;
use Potato\Zendesk\Model\Source\TicketStatus;
use Potato\Zendesk\Api\Data\TicketInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Potato\Zendesk\Model\Config;

class History extends Template
{
    /** @var string  */
    protected $_template = 'ticket/history.phtml';

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var TicketManagement  */
    protected $ticketManagement;
    
    /** @var null|array  */
    protected $ticketList = null;
    
    /** @var ZendeskDate  */
    protected $zendeskDate;
    
    /** @var TicketStatus  */
    protected $ticketStatus;

    /** @var Config  */
    protected $config;

    /** @var OrderInterface  */
    protected $order;

    /**
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param TicketManagement $ticketManagement
     * @param ZendeskDate $zendeskDate
     * @param TicketStatus $ticketStatus
     * @param Config $config
     * @param OrderInterface $order
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        TicketManagement $ticketManagement,
        ZendeskDate $zendeskDate,
        TicketStatus $ticketStatus,
        Config $config,
        OrderInterface $order,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->ticketManagement = $ticketManagement;
        $this->zendeskDate = $zendeskDate;
        $this->ticketStatus = $ticketStatus;
        $this->config = $config;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Tickets'));
    }

    /**
     * @return array|null|TicketInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTicketList()
    {
        if (!($this->customerSession->isLoggedIn())) {
            return [];
        }
        if (!$this->ticketList) {
            $customerId = $this->customerSession->getCustomerId();
            $store = $this->_storeManager->getStore();
            $this->ticketList = $this->ticketManagement->getTicketListByCustomerId($customerId, $store);
        }
        return $this->ticketList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    /**
     * @param int $ticketId
     * @return string
     */
    public function getViewUrl($ticketId)
    {
        return $this->getUrl('po_zendesk/ticket/view', ['ticket_id' => $ticketId]);
    }

    /**
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('po_zendesk/ticket/new');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
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

    /**
     * @return bool
     */
    public function getOrderNumberField()
    {
        return (bool)$this->config->getOrderNumberFieldId();
    }

    /**
     * @param $orderIncrementId
     * @return string
     */
    public function getOrderUrl($orderIncrementId)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->order->loadByIncrementId($orderIncrementId);
            $id = $order->getEntityId();
        } catch (\Exception $e) {
            $this->_logger->critical('Order %1 not found', $orderIncrementId);
            $id = null;
        }
        return $this->getUrl('sales/order/view', ['order_id' => $id]);
    }
}
