<?php

namespace Potato\Zendesk\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Potato\Zendesk\Lib\Zendesk\API\HttpClient as ZendeskAPI;
use Potato\Zendesk\Lib\Zendesk\API\Exceptions\AuthException;
use Potato\Zendesk\Model\Config as SystemConfig;

class Authorization
{
    const ZENDESK_DEFAULT_PROTOCOL = 'https://';
    const ZENDESK_DEFAULT_HOSTNAME = '.zendesk.com';

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var Config  */
    protected $config;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var SystemConfig */
    protected $systemConfig;

    /** @var null|ZendeskAPI  */
    private $client = null;

    /**
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param LoggerInterface $logger
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        LoggerInterface $logger,
        Config $config,
        StoreManagerInterface $storeManager,
        SystemConfig $systemConfig
    ) {
        $this->scopeConfig = $scopeConfigInterface;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param null|array $postData
     * @return null|integer|Store|Website
     */
    public function isAuth($postData)
    {
        $result = null;
        if(null === $postData || !isset($postData['token'])) {
            $this->logger->error('No authorisation token provided.');
            return $result;
        }
        //check is default token
        $storeToken =  $this->systemConfig->getApiTokenForDefault();
        if($postData['token'] == $storeToken) {
            $result = Store::DEFAULT_STORE_ID;
            return $result;
        }
        //check for websites
        $allWebs = $this->storeManager->getWebsites();
        foreach ($allWebs as $website) {
            $storeToken = $this->systemConfig->getApiTokenForWebsite($website);
            if($postData['token'] == $storeToken) {
                $result = $website;
                return $result;
            }
        }
        //check for stores
        $allStores = $this->storeManager->getStores();
        foreach ($allStores as $store) {
            $storeToken =  $this->systemConfig->getApiTokenForStore($store);
            if($postData['token'] == $storeToken) {
                $result = $store;
                return $result;
            }
        }
        $this->logger->error('Authorisation failed.');
        return $result;
    }

    /**
     * @param null|integer|Store $store
     * @return null|ZendeskAPI
     */
    public function connectToZendesk($store = null)
    {
        if (null !== $this->client) {
            return $this->client;
        }
        $subdomain = $this->systemConfig->getSubdomain($store);
        $username = $this->systemConfig->getAgentEmail($store);
        $token = $this->systemConfig->getAgentToken($store);
        $this->client = new ZendeskAPI($subdomain);
        try {
            $this->client->setAuth('basic', ['username' => $username, 'token' => $token]);
        } catch (AuthException $e) {
            $this->logger->error($e->getMessage());
            return null;
        }
        return $this->client;
    }

    /**
     * @param string $object
     * @param null|int $id
     * @param string $format
     * @param null|integer|Store $store
     * @return string
     */
    public function getZendeskUrl($object = '', $id = null, $format = 'old', $store = null)
    {
        $protocol = self::ZENDESK_DEFAULT_PROTOCOL;
        $hostname = self::ZENDESK_DEFAULT_HOSTNAME;

        $domain = $this->systemConfig->getSubdomain($store);
        $root = ($format === 'old') ? '' : '/agent/#';

        $base = $protocol . $domain . $hostname . $root;
        switch($object) {
            case '':
                return $base;
                break;
            case 'ticket':
                return $base . '/tickets/' . $id;
                break;
            case 'user':
                return $base . '/users/' . $id;
                break;
            case 'raw':
                return $protocol . $domain . '/' . $id;
                break;
        }
        return $base;
    }
}
