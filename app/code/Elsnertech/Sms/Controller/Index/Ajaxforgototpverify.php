<?php
namespace Elsnertech\Sms\Controller\Index;

use Magento\Framework\App\Action\Context;
class Ajaxforgototpverify extends \Magento\Framework\App\Action\Action
{
	public $_helperdata;
	public function __construct(
		Context $context,
		\Elsnertech\Sms\Helper\Data $helperData
	)
    {
        $this->_helperdata = $helperData;
        parent::__construct($context);
    }
	public function execute()
    {
		$data = $this->getRequest()->getParams();
		$returnVal = $this->_helperdata->verfiyForgotOtp($data['mobile'],$data['otp'],$data['countrycode']);
		echo $returnVal;
    }
}