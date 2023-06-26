<?php
 
namespace Elsnertech\Sms\Controller\Index;
use Magento\Framework\App\Action\Context;
use Elsnertech\Sms\Model\ForgototpmodelFactory;
use Magento\Customer\Model\CustomerFactory;
class Ajaxupdatepassotp extends \Magento\Framework\App\Action\Action
{
    protected $_ForgototpmodelFactory;
	protected $_CustomerFactory;
	public function __construct(
		Context $context,
		ForgototpmodelFactory $ForgototpmodelFactory,		
		CustomerFactory $CustomerFactory,
		\Elsnertech\Sms\Helper\Data $helperData,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	){
       $this->_ForgototpmodelFactory = $ForgototpmodelFactory;
	   $this->_CustomerFactory = $CustomerFactory;
	   $this->_helperdata = $helperData;
	   $this->resultJsonFactory = $resultJsonFactory;
    	parent::__construct($context);
    }
   public function execute()
    {
		$helperData = $this->_objectManager->create('Elsnertech\Sms\Helper\Data');		
		$randomCode = $helperData->generateRandomString();
		$message = $helperData->getForgotOtpMessage($randomCode);
		$mobile = $this->getRequest()->get('mobile');
		$countryCode = $this->getRequest()->get('countrycode');
		$otp = $this->getRequest()->get('otp');
		$newpass = $this->getRequest()->get('newpass');
		$isVerify = $this->_helperdata->verfiyForgotOtp($mobile,$otp,$countryCode);
		if($isVerify == "true"){
			$customerCount = $this->_CustomerFactory->create()->getCollection()->addFieldToFilter("mobilenumber", $countryCode.$mobile);
			if(count($customerCount) == 1){
				$customer = $customerCount->getFirstItem();
				$custom = $this->_CustomerFactory->create();
				$custom = $custom->setWebsiteId($helperData->getWebsiteId());
				$custom = $custom->loadByEmail($customer->getEmail());
				$custom->setRpToken($customer->getRpToken());	
				$custom->setPassword($newpass);	
				$custom->save();
			} 
			$resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData('true');
		}
		$resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData('false');
    }
}