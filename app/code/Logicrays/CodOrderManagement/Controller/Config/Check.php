<?php

namespace Logicrays\CodOrderManagement\Controller\Config;

use Logicrays\CodOrderManagement\Helper\OtpPopupData;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $_resultJsonFactory
     * @param OtpPopupData $_otpHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $_resultJsonFactory,
        OtpPopupData $_otpHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $_resultJsonFactory;
        $this->_otpHelper = $_otpHelper;
        return parent::__construct($context);
    }

    /**
     * Checks wheather order otp popup is enabled or not
     *
     * @return boolean
     */
    public function execute()
    {
        $result = false;
        $postData = $this->getRequest()->getParams();
        $response = $this->_resultJsonFactory->create();
        if (isset($postData['paymentMethod'])) {
            $result = $this->_otpHelper->verifyOrderOtpPopupConfig($postData['paymentMethod']);
        }
        $response->setData(["success" => $result]);
        return $response;
    }
}
