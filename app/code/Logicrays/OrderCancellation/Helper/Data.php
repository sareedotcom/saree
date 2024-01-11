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
    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $customer;
    protected $orderRepository;
    public $scopeConfig;
    protected $order;

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
    )
    {
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

    public function modEnable()
    {
        return $this->scopeConfig->getValue(
            'order/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
    }

    public function sendEmail($orderId, $selected_option, $attachedImages)
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
        if($selected_option == 'cancel_entire_order') {
            $templateId = $this->scopeConfig->getValue(
                'order/cancellation/email_template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );
        }
        else {
            $templateId = 'order_item_cancellation_email_template';
        }
        $toEmails = [$customerEmail, $adminEmail];

        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($attachedImages);

            if($selected_option == 'cancel_entire_order') {
                $templateVars = [
                    'increment_id' => $orderIncrementId,
                    'customer_name' => $customerName,
                    'order_id' => $orderId
                ];
            }
            else {
                $templateVars = [
                    'increment_id' => $orderIncrementId,
                    'customer_name' => $customerName,
                    'order_id' => $orderId
                ];
            }

            if(count($attachedImages)){
                $templateVars['img1'] = "";
                $templateVars['img2'] = "";
                if(isset($attachedImages['img1'])){
                    $templateVars['img1'] = $attachedImages['img1'];
                }
                if(isset($attachedImages['img2'])){
                    $templateVars['img2'] = $attachedImages['img2'];
                }
                
            }

            $logger->info($templateVars);

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