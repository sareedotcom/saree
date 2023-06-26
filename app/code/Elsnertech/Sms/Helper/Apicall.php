<?php
namespace Elsnertech\Sms\Helper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use Twilio\Rest\ClientFactory;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $scopeConfig;
    protected $_storeManager;
    protected $_clientFactory;

	public function __construct(
		ScopeConfigInterface $scopeConfig,
		StoreManagerInterface $storeManager,
		ClientFactory $clientFactory
		)
	{
		$this->scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_clientFactory = $clientFactory;

	}
	public function isEnable()
	{
		return $this->scopeConfig->getValue(
            'sms/moduleoption/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function getAuthkey()
	{
	   return $this->scopeConfig->getValue(
            'sms/general/authkey',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function getRouttype()
	{
		 return $this->scopeConfig->getValue(
            'sms/general/routtype',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getPassword()
	{
		return $this->scopeConfig->getValue(
            'sms/general/password',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getApiUrl()
	{
		return $this->scopeConfig->getValue(
            'sms/general/apiurl',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getSenderId()
	{
		return $this->scopeConfig->getValue(
            'sms/general/senderid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getAccountSID()
	{
		return $this->scopeConfig->getValue(
            'sms/general/accountsid',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getAuthToken()
	{
		return $this->scopeConfig->getValue(
            'sms/general/authtoken',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getTwilioNumber()
	{
		return $this->scopeConfig->getValue(
            'sms/general/twilionumber',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function curlApiCall($message,$mobilenumbers,$countryCode)
	{
		if ($this->isEnable()) {
			/*$sid = "ACfb6fe892d18a9ee65bc1dcc5f9b4b87d";
			$token = "1b25e8cf3f45a324090bb16bafd14c19";*/
			// $client = new Twilio\Rest\Client($sid, $token);
			$client = $this->_clientFactory->create([
			            'username' => $this->getAccountSID(),
			            'password' => $this->getAuthToken(),
			        ]);
			// $countryCode = '+91';
			$messages = $client->messages->create(
				$countryCode.$mobilenumbers,
				array(
					'from' => $this->getTwilioNumber(),
					'body' => $message
				)
			);

			if ($messages->sid) {
				return "true";
			}
			if ($messages->error_code) {
				return $messages->error_message;
			}

			return "error";

			/*$postData = array(
				'authkey' => $this->getAuthkey(),
				'mobiles' => $mobilenumbers,
				'message' => urlencode($message),
				'sender' => $this->getSenderId(),
				'route' => $this->getRouttype()
			);

			$ch = curl_init();
			if (!$ch)
			{
				die("Couldn't initialize a cURL handle");
			}

			$ret = curl_setopt($ch, CURLOPT_URL,$this->getApiUrl());
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt ($ch, CURLOPT_POSTFIELDS,$postData);

			$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$curlresponse = curl_exec($ch); // execute

			if(curl_errno($ch))
				return "error";

			if (empty($ret))
			{
				die(curl_error($ch));
				curl_close($ch); // close cURL handler
				return "error";
			}
			else
			{
				$info = curl_getinfo($ch);
				curl_close($ch); // close cURL handler
			}
			return "true";*/
		} else {
			return "false";
		}
	}
}