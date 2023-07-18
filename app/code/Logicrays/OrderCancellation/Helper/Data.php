<?php
namespace Logicrays\OrderCancellation\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;

class Data extends AbstractHelper
{
    /**
     *
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     *
     * @var Session
     */
    protected $customer;

    /**
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     *
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     *
     * @var Order
     */
    protected $order;

    /**
     *
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Customer\Model\Customer $customers
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Order $order
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Order $order
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->customer = $customer;
        $this->_customers = $customers;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     *
     * Checking extension is enable or not
     */
    public function modEnable()
    {
        return $this->scopeConfig->getValue(
            'order/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
    }

    /**
     *
     * Sending mail
     */
    public function sendEmail($orderId, $selected_option)
    {
        $order = $this->orderRepository->get($orderId);
        $orderIncrementId = $order->getIncrementId();
        $customer = $this->customer;
        $customerData = $this->_customers->load($customer->getCustomerId());
        $customerName = $customerData->getFirstname()." ".$customerData->getLastname();
        $customerEmail = $customerData->getEmail();

        $adminEmail = $this->scopeConfig->getValue(
            'order/cancellation/receiver_mail_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
        $senderName = $this->scopeConfig->getValue(
            'order/cancellation/sender_name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
        $senderEmail = $this->scopeConfig->getValue(
            'order/cancellation/sender_mail_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
        if ($selected_option == 'cancel_entire_order') {
            $templateId = $this->scopeConfig->getValue(
                'order/cancellation/email_template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );
        } else {
            $templateId = 'order_item_cancellation_email_template';
        }
        $toEmails = [$customerEmail, $adminEmail];

        try {
            if ($selected_option == 'cancel_entire_order') {
                $templateVars = [
                    'increment_id' => $orderIncrementId,
                    'customer_name' => $customerName,
                    'order_id' => $orderId
                ];
            } else {
                $templateVars = [
                    'increment_id' => $orderIncrementId,
                    'customer_name' => $customerName,
                    'order_id' => $orderId
                ];
            }

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $senderEmail, 'name' => $senderName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmails)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}
