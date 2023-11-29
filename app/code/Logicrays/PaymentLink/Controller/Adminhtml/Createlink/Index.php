<?php

namespace Logicrays\PaymentLink\Controller\Adminhtml\Createlink;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{    
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    private $context;

    /**
     * @var \Logicrays\PaymentLink\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Logicrays\PaymentLink\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Logicrays\PaymentLink\Helper\Data $helperData
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->helperData = $helperData;
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        $whoteData = $this->context->getRequest()->getParams();
        if($whoteData['isAjax']){
            
            $minimumPay = $whoteData['minimumPay'];

            if($whoteData['paymentType'] == 'Stripe')
            {
                $paymentLink = $this->helperData->sendStipePaymentLink("INR", $minimumPay);
            }
            else if($whoteData['paymentType'] == 'Razorpay')
            {
                $paymentLink = $this->helperData->sendRozorPaymentLink($whoteData['orderId'], $minimumPay, $whoteData['customerName'], $whoteData['mobilenumber'], $whoteData['customerEmail']);
            }
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["message" => ($paymentLink), "suceess" => true]);
        return $resultJson;
    }
}