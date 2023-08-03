<?php

/**
 * Tatvic Software
 *
 * @category Tatvic
 * @package Tatvic_EnhanceEcommercePro
 * @author Tatvic
 * @copyright Copyright (c)  Tatvic Analytics LLC (https://www.tatvic.com)
 */
namespace Tatvic\ActionableGoogleAnalytics\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class SentMail
 * @package Tatvic\ActionableGoogleAnalytics\Observer
 */
class SendMail implements ObserverInterface
{

    const XML_CONFIG_EMAIL = 'tatvic_ee/general/email_id';

    const XML_CONFIG_PURCHASE_CODE = 'tatvic_ee/purchase_code/purchase_code';

    const XML_CONFIG_STATUS = 'tatvic_ee/general/enable';

    const XML_CONFIG_TOKEN = 'tatvic_ee/general/ref_token';


    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * SentMail constructor.
     * @param ScopeConfigInterface $scopeConfig
     */

    function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer){

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $email = $this->_scopeConfig->getValue(self::XML_CONFIG_EMAIL,'default', $storeScope);
        $refToken = $this->_scopeConfig->getValue(self::XML_CONFIG_TOKEN,'default',$storeScope);
        $purchaseCode = $this->_scopeConfig->getValue(self::XML_CONFIG_PURCHASE_CODE,'default',$storeScope);
        $status = $this->_scopeConfig->getValue(self::XML_CONFIG_STATUS,'default',$storeScope);
        $this->sendEmailToTatvic($email,$refToken,$purchaseCode,$status);
    }

    /**
     * @param $email
     * @param $purchaseCode
     * @param $refToken
     */

    public function sendEmailToTatvic($email,$refToken,$purchaseCode,$status){

        $fields_string = '';
        $url = "http://dev.tatvic.com/leadgen/woocommerce-plugin/store_email/actionable_ga/";
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $domain_name = $storeManager->getStore()->getBaseUrl();
        $code = !empty($purchaseCode) ? $purchaseCode : "";
        $status = $status == 0 ? "Inactive" : "Active";
        $acc_token = isset($refToken) ? $refToken : "Auth Token Not available";
        $fields = array(
            "email" => urlencode($email),
            "domain_name" => urlencode($domain_name),
            "tvc_tkn" => urlencode($acc_token),
            "store_type" => "Magento-2 AGA-GTM",
            "purchase_code" => urlencode($code),
            "status" => urlencode($status)
        );

        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        $ch = curl_init();

        /**
         * Set the url, number of POST vars, POST data
         */

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /**
         * execute post
         */

        curl_exec($ch);

        /**
         * close connection
         */

        curl_close($ch);
    }
}