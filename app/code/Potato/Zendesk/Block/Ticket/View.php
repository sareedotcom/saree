<?php
namespace Potato\Zendesk\Block\Ticket;

use Potato\Zendesk\Api\Data\TicketInterface;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Potato\Zendesk\Model\Source\ZendeskDate;
use Potato\Zendesk\Api\Data\MessageInterface;
use Potato\Zendesk\Api\Data\UserInterface;
use Potato\Zendesk\Model\Source\TicketStatus;

class View extends Template
{
    /** @var string  */
    protected $_template = 'ticket/view.phtml';

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var MessageInterface[]|array  */
    protected $messageList = [];

    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var ZendeskDate  */
    protected $zendeskDate;

    /** @var array  */
    protected $users = [];

    /**
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param TicketManagement $ticketManagement
     * @param ZendeskDate $zendeskDate
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        TicketManagement $ticketManagement,
        ZendeskDate $zendeskDate,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->ticketManagement = $ticketManagement;
        $this->zendeskDate = $zendeskDate;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Ticket # %1 "%2"', $this->getTicket()->getId(), $this->getTicket()->getSubject()));
        parent::_construct();
    }

    /**
     * @return TicketInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getTicket()
    {
        $store = $this->_storeManager->getStore();
        return $this->ticketManagement->getTicketById($this->getRequest()->getParam('ticket_id'), $store);
    }

    /**
     * @return array|null|MessageInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getMessageList()
    {
        if ($this->messageList) {
            return $this->messageList;
        }
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return $this->messageList;
        }
        /** @var TicketInterface $ticket */
        $ticket = $this->getTicket();
        if (null === $ticket) {
            return $this->messageList;
        }
        $store = $this->_storeManager->getStore();
        $this->messageList = $this->ticketManagement->getMessageListByTicketId($ticket->getId(), $store);
        return $this->messageList;
    }

    /**
     * @param int $authorId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getUserByAuthorId($authorId)
    {
        if (array_key_exists($authorId, $this->users)) {
            return $this->users[$authorId];
        }
        $store = $this->_storeManager->getStore();
        $this->users[$authorId] = $this->ticketManagement->getUserByAuthorId($authorId, $store);
        return $this->users[$authorId];
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
    public function getDefaultUserPhoto()
    {
        return $this->getViewFileUrl('Potato_Zendesk::images/default-avatar.png');
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
     * @param string $attachType
     * @return bool
     */
    public function isImageAttachment($attachType) {
        return (false !== strpos($attachType, 'image'));
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function isCanAnswer()
    {
        try {
            $status = $this->getTicket()->getStatus();
        } catch (\Exception $e) {
            return false;
        }
        return $status != TicketStatus::CLOSED_VALUE;
    }
}
