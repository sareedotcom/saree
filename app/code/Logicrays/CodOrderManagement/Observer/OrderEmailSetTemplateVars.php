<?php

namespace Logicrays\CodOrderManagement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Cart;
use Logicrays\CodOrderManagement\Helper\OtpPopupData;

class OrderEmailSetTemplateVars extends \Magento\Sales\Model\Order\Email\Sender implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Template
     */
    protected $templateContainer;
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    protected $cart;

    /**
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param Cart $cart
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        Cart $cart,
        OtpPopupData $_otpHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->paymentHelper = $paymentHelper;
        $this->orderResource = $orderResource;
        $this->cart = $cart;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
        $this->identityContainer = $identityContainer;
        $this->_otpHelper = $_otpHelper;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->templateContainer = $templateContainer;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transportObject = $observer->getEvent()->getTransport();
        $order = $transportObject->getOrder();
        $quote = $this->cart->getQuote();
        $payment_link = $this->_otpHelper->getPaymentLink();
        $isModuleEnable = $this->_otpHelper->isOrderOtpPopupEnable();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $paymentMethod = $method->getCode();
        if($isModuleEnable == 1 && $paymentMethod == 'cashondelivery'){
            $transportObject['link'] = $payment_link;
            $transportObject['linkText'] = 'Make Payment Here';
        }
    }
}