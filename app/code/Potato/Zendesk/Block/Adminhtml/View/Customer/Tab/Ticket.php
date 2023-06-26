<?php
namespace Potato\Zendesk\Block\Adminhtml\View\Customer\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Potato\Zendesk\Model\Config;
use Potato\Zendesk\Model\Source\ZendeskDate;
use Potato\Zendesk\Model\Source\TicketStatus;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Translate\InlineInterface;
use Potato\Zendesk\Api\Data\TicketInterface;
use Magento\Sales\Api\Data\OrderInterface;

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
    
    /** @var InlineInterface  */
    protected $translateInline;
    
    /** @var Config  */
    protected $config;

    /** @var OrderInterface  */
    protected $order;

    /**
     * @param Template\Context $context
     * @param Registry $registry
     * @param TicketManagement $ticketManagement
     * @param ZendeskDate $zendeskDate
     * @param TicketStatus $ticketStatus
     * @param InlineInterface $translateInline
     * @param Config $config
     * @param OrderInterface $order
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        TicketManagement $ticketManagement,
        ZendeskDate $zendeskDate,
        TicketStatus $ticketStatus,
        InlineInterface $translateInline,
        Config $config,
        OrderInterface $order,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ticketManagement = $ticketManagement;
        $this->zendeskDate = $zendeskDate;
        $this->ticketStatus = $ticketStatus;
        $this->translateInline = $translateInline;
        $this->config = $config;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
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
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('po_zendesk/customer/ticket', ['_current' => true]);
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
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }

    /**
     * @return array|null|TicketInterface[]
     */
    public function getTicketList()
    {
        if (!$this->getCustomerId()) {
            return [];
        }
        if (!$this->ticketList) {
            $this->ticketList = $this->ticketManagement->getTicketListByCustomerId($this->getCustomerId());
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

    /**
     * @return string
     */
    public function getProcessedResponseBody()
    {
        $html = $this->toHtml();
        $this->translateInline->processResponseBody($html);
        return $html;
    }

    /**
     * @return bool
     */
    public function getOrderNumberField()
    {
        return (bool)$this->config->getOrderNumberFieldId();
    }

    /**
     * @param int $orderIncrementId
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
