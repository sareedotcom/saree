<?php 
namespace Elsnertech\Sms\Helper;

use Elsnertech\Sms\Model\RegotpmodelFactory;
use Elsnertech\Sms\Model\LoginotpmodelFactory;
use Elsnertech\Sms\Model\ForgototpmodelFactory;
use Elsnertech\Sms\Helper\Apicall;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_EMAIL_ADMIN_QUOTE_SENDER = 'sms/generalsettings/adminemailsender';
	const XML_PATH_EMAIL_ADMIN_QUOTE_NOTIFICATION = 'sms/generalsettings/adminemailtemplate';
	const XML_PATH_EMAIL_ADMIN_NAME = 'Admin';
	const XML_PATH_EMAIL_ADMIN_EMAIL = 'sms/generalsettings/adminmailreceiver';
	const MOBILELOGIN_MODULEOPTION_ENABLE = 'sms/moduleoption/enable';
	const MOBILELOGIN_GENERALSETTINGS_LOGINNOTIFY = 'sms/generalsettings/loginnotify';
	const MOBILELOGIN_GENERALSETTINGS_OTP = 'sms/generalsettings/otp';
	const MOBILELOGIN_GENERALSETTINGS_OTPTYPE = 'sms/generalsettings/otptype';
	const MOBILELOGIN_FORGOTOTPSEND_MESSAGE = 'sms/forgototpsend/message';
	const MOBILELOGIN_OTPSEND_MESSAGE = 'sms/otpsend/message';
	const MOBILELOGIN_LOGINOTPSEND_MESSAGE = 'sms/loginotpsend/message';
	const MOBILELOGIN_GENERAL_AUTHKEY = 'sms/general/authkey';
	const MOBILELOGIN_GENERAL_ROUTTYPE = 'sms/general/routtype';
	const MOBILELOGIN_GENERAL_PASSWORD = 'sms/general/password';
	const MOBILELOGIN_GENERAL_APIURL = 'sms/general/apiurl';
	const MOBILELOGIN_GENERAL_SENDERID = 'sms/general/senderid';
	const MOBILELOGIN_GENERALSETTINGS_LOGINTYPE = 'sms/generalsettings/logintype';

	
	protected $_storeTime;
    protected $_storeManager;
	protected $_modelRegOtpFactory;
	protected $_modelLoginOtpFactory;
	protected $_modelForgotOtpFactory;
	protected $customerCollection;
	protected $inlineTranslation;
	protected $transportBuilder;
	
	protected $apicall;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		RegotpmodelFactory $modelRegOtpFactory,
		\Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,		
		\Magento\Framework\ObjectManagerInterface $objectManager,
		LoginotpmodelFactory $modelLoginOtpFactory,
		ForgototpmodelFactory $modelForgotOtpFactory,
		StateInterface $inlineTranslation,
		TransportBuilder $transportBuilder,
		Apicall $apicall
        )
	{
		$this->scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_modelRegOtpFactory = $modelRegOtpFactory;
		$this->objectManager = $objectManager;
		$this->customerCollection = $customerCollection;
		$this->_modelLoginOtpFactory = $modelLoginOtpFactory;
		$this->_modelForgotOtpFactory = $modelForgotOtpFactory;
		$this->inlineTranslation = $inlineTranslation;
		$this->transportBuilder = $transportBuilder;
		$this->apicall = $apicall;
	}
	public function isEnable()
	{
		return $this->scopeConfig->getValue(
            self::MOBILELOGIN_MODULEOPTION_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function isEnableLoginEmail()
	{
		return $this->scopeConfig->getValue(
            self::MOBILELOGIN_GENERALSETTINGS_LOGINNOTIFY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function generateRandomString()
	{
		$length = $this->getOtpStringlenght();
		if($this->getOtpStringtype() == "N"){
			$randomString = substr(str_shuffle("0123456789"), 0, $length);
		}
		else{
			$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);	
		}

		return $randomString;
	}

	public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
	public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
	 public function getStoreUrl($fromStore = true)
    {
        return $this->_storeManager->getStore()->getUrl();
    }
	 public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }
	
	public function getOtpStringlenght()
	{
		return $this->scopeConfig->getValue(
            self::MOBILELOGIN_GENERALSETTINGS_OTP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getOtpStringtype()
	{
		return $this->scopeConfig->getValue(
            self::MOBILELOGIN_GENERALSETTINGS_OTPTYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getNotificatonEnable()
	{
		return $this->scopeConfig->getValue(
           self::MOBILELOGIN_GENERALSETTINGS_LOGINNOTIFY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getForgotOtpTemplate()
	{
		return $this->scopeConfig->getValue(
            self::MOBILELOGIN_FORGOTOTPSEND_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getRegOtpTemplate()
	{
		return $this->scopeConfig->getValue(
			self::MOBILELOGIN_OTPSEND_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getLoginOtpTemplate()
	{
		return $this->scopeConfig->getValue(
			self::MOBILELOGIN_LOGINOTPSEND_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getForgotOtpMessage($randomCode)
	{
		$storeName = $this->getStoreName();
		$storeUrl = $this->getStoreUrl();

		$codes = array('{{shop_name}}','{{shop_url}}','{{random_code}}');
		$accurate = array($storeName,$storeUrl,$randomCode);
		return str_replace($codes,$accurate,$this->getForgotOtpTemplate());
	}
	public function sendOTPCode($mobile,$countryCode)
	{
	try{	
		$customerData = $this->objectManager->create('\Magento\Customer\Model\Customer');
		$customer = $customerData->getCollection()->addFieldToFilter("mobilenumber", $mobile) ;
		if(count($customer) > 0){
			return "exist";
		}
	
		$otpModels = $this->_modelRegOtpFactory->create();		
		$collection = $otpModels->getCollection();
		$collection->addFieldToFilter('mobile', $mobile);
		
		$objDate = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$date = $objDate->gmtDate();
		$randomCode = $this->generateRandomString();
		$message = $this->getRegOtpMessage($mobile,$randomCode);
		
		if(count($collection) == 0){
		
			$otpModel = $this->_modelRegOtpFactory->create();
			$otpModel->setRandomCode($randomCode);
			$otpModel->setCreatedTime($date);	
			$otpModel->setIsVerify(0);	
			$otpModel->setMobile($countryCode.$mobile);	
			$otpModel->save();	
		}else{
			
			$otpModel = $this->_modelRegOtpFactory->create()->load($mobile,'mobile');
			$otpModel->setRandomCode($randomCode);
			$otpModel->setCreatedTime($date);	
			$otpModel->setIsVerify(0);	
			$otpModel->setMobile($countryCode.$mobile);
			$otpModel->save();		
		}
		$apiReturn = $this->curlApiCall($message,$mobile,$countryCode);
    	return $apiReturn;
	
		}catch(\Exception $e)
		{
			return "false";
		}
	}
	
	public function sendLoginOTPCode($mobile,$countryCode)
	{
	try{	
		$customerData = $this->objectManager->create('\Magento\Customer\Model\Customer');
		$customer = $customerData->getCollection()->addFieldToFilter("mobilenumber", $countryCode.$mobile) ;
		if(count($customer) != 1){
			return "false";
		}
		$otpModels = $this->_modelLoginOtpFactory->create();		
		$collection = $otpModels->getCollection();
		$collection->addFieldToFilter('mobile', $countryCode.$mobile);
		$objDate = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$date = $objDate->gmtDate();
		$randomCode = $this->generateRandomString();
		// $message = $this->getRegOtpMessage($mobile,$randomCode);
		$message = $this->getLoginOtpMessage($mobile,$randomCode);
		
		if(count($collection) == 0){
		
			$otpModel = $this->_modelLoginOtpFactory->create();
			$otpModel->setRandomCode($randomCode);
			$otpModel->setCreatedTime($date);	
			$otpModel->setIsVerify(0);	
			$otpModel->setMobile($countryCode.$mobile);	
			$otpModel->save();	
		}else{
			
			$otpModel = $this->_modelLoginOtpFactory->create()->load($mobile,'mobile');
			$otpModel->setRandomCode($randomCode);
			$otpModel->setCreatedTime($date);	
			$otpModel->setIsVerify(0);	
			$otpModel->setMobile($countryCode.$mobile);
			$otpModel->save();		
		}
		$apiReturn = $this->curlApiCall($message,$mobile,$countryCode);
    	return $apiReturn;
		}catch(\Exception $e)
		{
			return "false";
		}
	}
	public function getAuthkey()
	{
	   return $this->scopeConfig->getValue(
	   		self::MOBILELOGIN_GENERAL_AUTHKEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	
	public function getRouttype()
	{
		 return $this->scopeConfig->getValue(
		 	self::MOBILELOGIN_GENERAL_ROUTTYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getPassword()
	{
		return $this->scopeConfig->getValue(
			self::MOBILELOGIN_GENERAL_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getApiUrl()
	{
		return $this->scopeConfig->getValue(
			self::MOBILELOGIN_GENERAL_APIURL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getSenderId()
	{
		return $this->scopeConfig->getValue(
			self::MOBILELOGIN_GENERAL_SENDERID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	
	public function getRegOtpMessage($mobile,$randomCode)
	{
		$storeId = $this->getSenderId();
		$storeName = $this->getStoreName();
		$storeUrl = $this->getStoreUrl();
		$codes = array('{{shop_name}}','{{shop_url}}','{{random_code}}');
		$accurate = array($storeName,$storeUrl,$randomCode);
		return str_replace($codes,$accurate,$this->getRegOtpTemplate());
		
	}
	public function getLoginOtpMessage($mobile,$randomCode)
	{
		$storeId = $this->getSenderId();
		$storeName = $this->getStoreName();
		$storeUrl = $this->getStoreUrl();
		$codes = array('{{shop_name}}','{{shop_url}}','{{random_code}}');
		$accurate = array($storeName,$storeUrl,$randomCode);
		// return str_replace($codes,$accurate,$this->getRegOtpTemplate());
		return str_replace($codes,$accurate,$this->getLoginOtpTemplate());
		
	}
	public function checkLoginOTPCode($mobile,$randome){
		
		$otpModels = $this->_modelLoginOtpFactory->create();		
		$collection = $otpModels->getCollection();
		$collection->addFieldToFilter('mobile', $mobile);
		$collection->addFieldToFilter('random_code', $randome);
		return count($collection);
		
	}
	public function verfiyForgotOtp($mobile,$otp,$countryCode){
		$otpModels = $this->_modelForgotOtpFactory->create();		
		$collection = $otpModels->getCollection();
		$collection->addFieldToFilter('mobile', $countryCode.$mobile);
		$collection->addFieldToFilter('random_code', $otp);
		if(count($collection) == 1)
		{
			$forgototp = $collection->getFirstItem();
			$forgototp->setIsVerify(1);
			$forgototp->save();
			return "true";
		}else{
			return "false";
		}
		
	}
	public function getLoginType()
	{
	   return $this->scopeConfig->getValue(
	   		self::MOBILELOGIN_GENERALSETTINGS_LOGINTYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function sendMail($remoteId, $mail, $userAgent, $name = '')
	{
		// Send Mail To Admin For This
		$objDate = $this->objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$date = $objDate->gmtDate();

		$browser = $this->get_browser_name($userAgent);
			$this->inlineTranslation->suspend();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder
               ->setTemplateIdentifier($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_NOTIFICATION, $storeScope))
			   ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
               ->setTemplateVars([
                    'ip'  => $remoteId,
					'email' => $mail,
					'name' => $name,
					'datetime' => $date,
					'browser' => $browser
            	])
               ->setFrom($this->scopeConfig->getValue(self::XML_PATH_EMAIL_ADMIN_QUOTE_SENDER, $storeScope))
               ->addTo($mail)
               ->getTransport();

            $transport->sendMessage();
			$this->inlineTranslation->resume();
			return "true";
	}
	public function get_browser_name($user_agent)
	{
		if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
		elseif (strpos($user_agent, 'Edge')) return 'Edge';
		elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
		elseif (strpos($user_agent, 'Safari')) return 'Safari';
		elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
		elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
		
		return 'Unknown Broswer';
	}
	public function curlApiCall($message,$mobilenumbers,$countryCode)
	{
		return $this->apicall->curlApiCall($message,$mobilenumbers,$countryCode);
	}
}