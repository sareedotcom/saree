<?php
namespace Elsnertech\Sms\Controller\Index;

use Magento\Framework\App\Action\Context;
use Elsnertech\Sms\Model\LoginotpmodelFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;
class AjaxVerifyOtpForLogin extends \Magento\Framework\App\Action\Action
{
	protected $_modelLoginOtpFactory;
	public $_helperdata;
	protected $session;
	public function __construct(
		Context $context,
		LoginotpmodelFactory $modelLoginOtpFactory,
		\Elsnertech\Sms\Helper\Data $helperData,
		Session $customerSession

	){
		$this->_modelLoginOtpFactory = $modelLoginOtpFactory;
		$this->_helperdata = $helperData;
		$this->session = $customerSession;
        parent::__construct($context);
    }
	public function execute()
    {
		$data = "false";
		$mobileNumber = $this->getRequest()->get('mobile');
		$countryCode = $this->getRequest()->get('countrycode');
		$mobile = $countryCode.$mobileNumber;
		$otp = $this->getRequest()->get('otp');
		$isExist = $this->_helperdata->checkLoginOTPCode($mobile,$otp);
		if($isExist == 1){
			$customerData = $this->_objectManager->create('\Magento\Customer\Model\Customer');
			$customer = $customerData->getCollection()->addFieldToFilter("mobilenumber", $mobile)->getFirstItem();
			if($customer){
				$this->session->setCustomerAsLoggedIn($customer);
				$this->session->regenerateId();
				$data = "true";
				if($this->_helperdata->isEnableLoginEmail()){
					$this->_helperdata->sendMail($_SERVER['REMOTE_ADDR'],$customer->getEmail(),$_SERVER['HTTP_USER_AGENT'],$customer->getName());
				}
			}
		}
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($data);
		return $resultJson;
	}
}