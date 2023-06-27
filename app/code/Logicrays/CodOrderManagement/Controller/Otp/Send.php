<?php

namespace Logicrays\CodOrderManagement\Controller\Otp;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Logicrays\CodOrderManagement\Helper\OtpPopupData;

class Send extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param JsonFactory $_resultJsonFactory
     * @param OtpPopupData $_otpHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $_resultJsonFactory,
        OtpPopupData $_otpHelper
    ) {
        $this->_resultJsonFactory = $_resultJsonFactory;
        $this->_otpHelper = $_otpHelper;
        parent::__construct($context);
    }

    /**
     * Sends Order OTP
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        try {
            $postData = $this->getRequest()->getParams();
            $response = $this->_resultJsonFactory->create();
            $isResend = isset($postData['resend']) ? (string) $postData['resend'] : 'false';
            $result = $this->_otpHelper->executeOtpOperation('send', $isResend);
            $response->setData(["success" => $result['status'], "message" => $result['message']]);

            return $response;
        } catch (Exception $r) {
            $logger->info($r->getMessage());
        }
    }
}
