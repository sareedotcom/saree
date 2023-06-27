<?php

namespace Logicrays\CodOrderManagement\Controller\Otp;

use Magento\Framework\Controller\Result\JsonFactory;
use Logicrays\CodOrderManagement\Helper\OtpPopupData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Verify extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param JsonFactory $_resultFactory
     * @param OtpPopupData $_otpHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $_resultFactory,
        OtpPopupData $_otpHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_resultFactory = $_resultFactory;
        $this->_otpHelper = $_otpHelper;
        return parent::__construct($context);
    }

    /**
     * Verifies Order OTP
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $response = $this->_resultFactory->create();
        $result = ['status' => false, 'message' => 'Unable to verify OTP, Please try again later.'];
        if (isset($postData['otp']) && !empty($postData['otp'])) {
            $result = $this->_otpHelper->executeOtpOperation('verify', $postData['otp']);
        }
        $response->setData(["success" => $result['status'], 'message' => $result['message']]);
        return $response;
        exit();
    }
}
