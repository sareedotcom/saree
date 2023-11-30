<?php


namespace Logicrays\PaymentLink\Block\Adminhtml\Order;

/**
 * Class ModalBox
 *
 * @package Logicrays\PaymentLink\Block\Adminhtml\Order
 */
class ModalBox extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Url $backendUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->backendUrl = $backendUrl;
    }

    public function getCurrentUrl()
    {
        $orderId = false;
        if($this->hasData('order')){
            $orderId = $this->getOrder()->getId();
        }
        // return $this->getUrl('paymentlink/sendreminder/index');
        return $this->getUrl('sales/order/view',[
            'order_id' => $orderId
        ]);
    }

    public function getFormUrl()
    {
        return $this->getUrl('paymentlink/sendreminder/index');
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getBackendUrl()
    {
        return $this->backendUrl->getUrl('paymentlink/createlink/index');
    }

    public function getPaymentReminderData()
    {
        $paymentData = [];
        if($this->hasData('order')){
            $order = $this->getOrder();
            $paymentData['grandTotal'] = $order->getBaseGrandTotal();
            $paymentData['totalDue'] = $order->getBaseTotalDue();
            $paymentData['currency'] = $order->getBaseCurrencyCode();
            $paymentData['orderId'] = $order->getIncrementId();
            $paymentData['customerName'] = $order->getCustomerName();
            $paymentData['customerEmail'] = $order->getCustomerEmail();
            $paymentData['mobilenumber'] = $order->getBillingAddress()->getTelephone();
        }
        return $paymentData;
    }
}

