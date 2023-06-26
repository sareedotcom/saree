<?php
 
namespace Elsnertech\Sms\Controller\Index;
use Magento\Framework\App\Action\Context;
use Elsnertech\Sms\Model\ForgototpmodelFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
class Ajaxforgot extends \Magento\Framework\App\Action\Action
{
   
    protected $_ForgototpmodelFactory;
	protected $_CustomerFactory;
	public function __construct(
		Context $context,
		ForgototpmodelFactory $ForgototpmodelFactory,		
		CustomerFactory $CustomerFactory
	){
       $this->_ForgototpmodelFactory = $ForgototpmodelFactory;
	   $this->_CustomerFactory = $CustomerFactory;
        parent::__construct($context);
    }
   public function execute()
    {
		$helperData = $this->_objectManager->create('Elsnertech\Sms\Helper\Data');		
		$randomCode = $helperData->generateRandomString();
		$message = $helperData->getForgotOtpMessage($randomCode);
		$mobile = $this->getRequest()->get('mobile');
		$countryCode = $this->getRequest()->get('countrycode');
		$objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$date = $objDate->gmtDate();
		
		$customerCount = $this->_CustomerFactory->create()->getCollection()->addFieldToFilter("mobilenumber", $countryCode.$mobile);
		
		$response = "false";
		if(count($customerCount) == 1 ){
				$otpModels = $this->_ForgototpmodelFactory->create();		
				$collection = $otpModels->getCollection();
				$collection->addFieldToFilter('mobile', $countryCode.$mobile);
				$customer = $customerCount->getFirstItem();
				if(count($collection) == 0 ){
					$forgotTable = $this->_ForgototpmodelFactory->create();		
					$forgotTable->setRandomCode($randomCode);
					$forgotTable->setCreatedTime($date);
					$forgotTable->setMobile($countryCode.$mobile);
					$forgotTable->setEmail($customer->getEmail());
					$forgotTable->setIpaddress($_SERVER['REMOTE_ADDR']);
					$forgotTable->setIsVerify(0);
					$forgotTable->save();
				}else{
					$forgotTable = $this->_ForgototpmodelFactory->create()->load($mobile,'mobile');;		
					$forgotTable->setRandomCode($randomCode);
					$forgotTable->setCreatedTime($date);
					$forgotTable->setMobile($countryCode.$mobile);
					$forgotTable->setEmail($customer->getEmail());
					$forgotTable->setIpaddress($_SERVER['REMOTE_ADDR']);
					$forgotTable->setIsVerify(0);
					$forgotTable->save();
				}
				 $helperData->curlApiCall($message,$mobile,$countryCode);
			$response = "true";	
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
	        $resultJson->setData($response);
    	    return $resultJson;
		}
	  
       $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
	   $resultJson->setData($response);
       return $resultJson;
    }
}