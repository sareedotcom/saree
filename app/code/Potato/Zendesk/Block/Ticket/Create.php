<?php
namespace Potato\Zendesk\Block\Ticket;

use Magento\Framework\View\Element\Template;
use Potato\Zendesk\Api\TicketManagementInterface as TicketManagement;
use Magento\Framework\Data\Form\FormKey;
use Potato\Zendesk\Api\Data\TicketInterface;
use Potato\Zendesk\Model\Config;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;

class Create extends Template
{
    /** @var string  */
    protected $_template = 'ticket/create.phtml';

    /** @var TicketManagement  */
    protected $ticketManagement;

    /** @var FormKey  */
    protected $formKey;

    /** @var Config  */
    protected $config;

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var OrderCollection  */
    protected $orderCollection;

    /** @var Registry|null  */
    protected $coreRegistry = null;

    /**
     * @param Template\Context $context
     * @param TicketManagement $ticketManagement
     * @param FormKey $formKey
     * @param Config $config
     * @param CustomerSession $customerSession
     * @param Registry $registry
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        TicketManagement $ticketManagement,
        FormKey $formKey,
        Config $config,
        CustomerSession $customerSession,
        Registry $registry,
        OrderCollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ticketManagement = $ticketManagement;
        $this->formKey = $formKey;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $registry;
        $this->orderCollection = $orderCollectionFactory->create();
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        if ($ticket = $this->getTicket()) {
            $this->pageConfig->getTitle()->set(__('Ticket # %1 "%2"', $ticket->getId(), $ticket->getSubject()));
        } else {
            $this->pageConfig->getTitle()->set(__('Create new ticket'));
        }
        parent::_construct();
    }

    /**
     * @return null|TicketInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getTicket()
    {
        if (!$ticketId = $this->getRequest()->getParam('ticket_id', null)) {
            return null;
        }
        $store = $this->_storeManager->getStore();
        return $this->ticketManagement->getTicketById($ticketId, $store);
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return bool
     */
    public function getOrderField()
    {
        return $this->config->getOrderNumberFieldId();
    }

    /**
     * @return bool
     */
    public function isDropdownSubject()
    {
        return $this->config->isSubjectFieldDropdown();
    }

    /**
     * @return array
     */
    public function getDropdownSubjectFields()
    {
        return $this->config->getSubjectDropdownContent();
    }

    /**
     * @return bool
     */
    public function isDropdownOrder()
    {
        return $this->config->isOrderFieldDropdown();
    }

    /**
     * @return array
     */
    public function getDropdownOrdersFields()
    {
        if ($this->getIsAdmin()) {
            $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        } else {
            $customerId = $this->customerSession->getCustomerId();
        }
        
        if (!$customerId) {
            return [];
        }
        $orderList = $this->orderCollection
            ->addAttributeToSelect('increment_id')
            ->addAttributeToFilter('customer_id', ['eq' => $customerId])
            ->addAttributeToSort('created_at', 'desc')
            ->getItems();
            
        $result = [];
        foreach ($orderList as $order) {
            $result[] = $order->getIncrementId();
        }
        return $result;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFormData()
    {
        if ($this->getIsAdmin()) {
            $session = ObjectManager::getInstance()->get(SessionManagerInterface::class);
        } else {
            $session = $this->customerSession;
        }
        $data = $session->getTicketFormData();
        $formData = new \Magento\Framework\DataObject();
        if (!empty($data)) {
            $formData->setData($data);
            $session->unsTicketFormData();
        }
        return $formData;
    }
}
