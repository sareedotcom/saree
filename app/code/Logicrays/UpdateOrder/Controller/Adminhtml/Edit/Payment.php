<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Logicrays\UpdateOrder\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartManagementInterface;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Controller\Adminhtml\AbstractAction;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Psr\Log\LoggerInterface;
use Magento\Directory\Model\CurrencyFactory;

/**
 * Class Payment
 */
class Payment extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Payment
{
    const ADMIN_RESOURCE = 'MageWorx_OrderEditor::edit_payment';

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepageCheckout;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $params;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Payment constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     * @param OrderRepositoryInterface $orderRepository
     * @param MsiStatusManager $msiStatusManager
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param CartManagementInterface $cartManagement
     * @param DataObjectFactory $dataObjectFactory
     * @param Onepage $onepageCheckout
     * @param JsonHelper $jsonHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        QuoteRepositoryInterface $quoteRepository,
        ShippingModel $shipping,
        PaymentModel $payment,
        OrderRepositoryInterface $orderRepository,
        MsiStatusManager $msiStatusManager,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        CartManagementInterface $cartManagement,
        DataObjectFactory $dataObjectFactory,
        Onepage $onepageCheckout,
        JsonHelper $jsonHelper,
        LoggerInterface $logger,
        CurrencyFactory $currencyFactory
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $helper,
            $scopeConfig,
            $quoteRepository,
            $shipping,
            $payment,
            $orderRepository,
            $msiStatusManager,
            $inventoryDetectionStatusManager,
            $cartManagement,
            $dataObjectFactory,
            $onepageCheckout,
            $jsonHelper,
            $logger

        );
        $this->eventManager      = $context->getEventManager();
        $this->cartManagement    = $cartManagement;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->onepageCheckout   = $onepageCheckout;
        $this->jsonHelper        = $jsonHelper;
        $this->logger            = $logger;
        $this->params            = $this->getRequest()->getParams();
        $this->currencyFactory   = $currencyFactory;
    }

    /**
     * @throws \Exception
     */
    protected function update()
    {
        $this->updatePaymentMethod();
    }

    /**
     * @throws \Exception
     */
    protected function updatePaymentMethod()
    {
        $order = $this->orderRepository->getById($this->params['order_id']);
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();

        $codCharge = $this->scopeConfig->getValue('payment/cashondelivery/fee',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $rateToBase = $this->currencyFactory->create()->load($order->getBaseCurrencyCode())->getAnyRate($order->getOrderCurrencyCode());
        $discountedAmount = $codCharge * $rateToBase;

       if($this->params['payment_method'] != 'cashondelivery'){

            $order->setCashOnDeliveryFee(0.0000);
            $order->setBaseCashOnDeliveryFee(0.0000);

            if($method->getCode() == 'cashondelivery'){
                $order->setGrandTotal($order->getGrandTotal() - $discountedAmount);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() - $codCharge);
            }
            $order->save();
        }
        else{
            
            if($order->getCashOnDeliveryFee() > 0){
                $order->setGrandTotal($order->getGrandTotal());
                $order->setBaseGrandTotal($order->getBaseGrandTotal());    
            }
            else{
                $order->setGrandTotal($order->getGrandTotal() + $discountedAmount);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() + $codCharge);
            }
            $order->setCashOnDeliveryFee($discountedAmount);
            $order->setBaseCashOnDeliveryFee($codCharge);
            
            $order->save();
        }

        $this->payment->initParams($this->params);

        $this->payment->updatePaymentMethod();
        $this->prepareDirectpostResponse();
    }

    /**
     * @return string
     */
    protected function prepareResponse(): string
    {
        return static::ACTION_RELOAD_PAGE;
    }

    /**
     * Prepare data for response
     *
     * @return void
     */
    protected function prepareDirectpostResponse()
    {
        $result   = $this->dataObjectFactory->create();
        $response = $this->getResponse();

        try {
            $result->setData('success', true);
            if (isset($this->params['order_id'])) {
                $result->setData('order_id', $this->params['order_id']);
            }

            $this->eventManager->dispatch(
                'mageworx_ordereditor_directpost',
                [
                    'result' => $result,
                    'action' => $this
                ]
            );
        } catch (LocalizedException $exception) {
            $this->logger->critical($exception);
            $result->setData('error', true);
            $result->setData('error_messages', $exception->getMessage());
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $result->setData('error', true);
            $result->setData(
                'error_messages',
                __('An error occurred on the server. Please try to place the order again.')
            );
        }
        if ($response instanceof Http) {
            $response->representJson($this->jsonHelper->jsonEncode($result));
        }
    }
}
