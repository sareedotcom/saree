<?php

namespace Logicrays\WhatsAppApi\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * BusinessOnBot api config path
     */
    const BUSINESS_ON_BOT_API_KEY = "businessonbot/businessonbot/x_api_key";

    /**
     * BusinessOnBot is enable or not config path
     */
    const BUSINESS_ON_BOT_IS_ENABLE_KEY = "businessonbot/businessonbot/enable";

    /**
     * Construct function
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfigValue($path) {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    public function businessOnBotCurl($method,$url,$requestPayload) {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/businessOnBotCurl.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('11111111111');
        
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $apiKey = $this->scopeConfig->getValue(Self::BUSINESS_ON_BOT_API_KEY, $storeScope);
        $logger->info('22222222');
        $client = new \Zend_Http_Client();
        $client->setUri($url);
        $logger->info('3333333');
        if ($method == 'POST') {
            $logger->info('444444');
            $client->setParameterPost($requestPayload);
        }
        $headers = ["Content-Type" => "application/json", "Accept" => "application/json",CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => 1, 'x-api-key' => $apiKey];
        $logger->info('555555');
        $client->setHeaders($headers);
        $response = $client->request($method)->getBody();
        $logger->info($response);
        $logger->info('66666666666');
    }

    public function isEnable(){
        return true;
    }
}
