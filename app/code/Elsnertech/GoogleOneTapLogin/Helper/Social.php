<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Helper;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Elsnertech\GoogleOneTapLogin\Helper\Data as HelperData;

/**
 * Class Social
 *
 * Elsnertech\GoogleOneTapLogin\Helper
 */
class Social extends HelperData
{
    private const ENABLE = "googleonetaplogin/general/enable";
    private const CLIENT_ID = "googleonetaplogin/general/clientid";
    private const CLOSE_PROMPT = "googleonetaplogin/general/closeprompt";
    private const CLIENT_SECRATE = "googleonetaplogin/general/clientsecrate";
    private const CALL_BACK = 'googleonetaplogin/result/callback';

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var configInterface
     */
    protected $configInterface;

    /**
     * @var httpContext
     */
    private $httpContext;

    /**
     * @var state
     */
    protected $state;

    /**
     * For __construct function
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $configInterface
     * @param State $state
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigInterface $configInterface,
        State $state,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->httpContext = $httpContext;
        $this->configInterface = $configInterface;
        $this->storeManager = $storeManager;
        $this->state = $state;
        parent::__construct($context, $httpContext);
    }
    
    /**
     * For get base auth url function
     *
     * @param undefined $area
     * @return string
     */
    public function getBaseAuthUrl($area = null)
    {
        $storeId = $this->getScopeUrl();
        $store = $this->storeManager->getStore($storeId);

        return $this->_getUrl(
            self::CALL_BACK,
            [
                '_nosid'  => true,
                '_scope'  => $storeId,
                '_secure' => true
            ]
        );
    }

    /**
     * For get scope url function
     *
     * @return int
     */
    protected function getScopeUrl()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = $this->storeManager->getWebsite($website)->getDefaultStore()->getId();
        }

        return $scope;
    }

    /**
     * For get social config function
     *
     * @param string $type
     * @return array
     */
    public function getSocialConfig($type)
    {
        $apiData = [
            'Google'    => ['scope' => 'profile email']
        ];

        if ($type && array_key_exists($type, $apiData)) {
            return $apiData[$type];
        }

        return [];
    }

    /**
     * For get config value function
     *
     * @param string $field
     * @param bool $scopeValue
     * @param string $scopeType
     * @return string
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if ($scopeValue === null && !$this->isArea()) {
   
            if (!$this->backendConfig) {
                $this->backendConfig = $configInterface;
            }

            return $this->backendConfig->getValue($field);
        }

        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * For is area function
     *
     * @param object $area
     * @return boolean
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {

            try {
                $this->isArea[$area] = ($this->state->getAreaCode() == $area);
            } catch (Exception $e) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * For is enabled function
     *
     * @param int $storeId
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue(self::ENABLE, $storeId);
    }

    /**
     * For get app id function
     *
     * @param int $storeId
     * @return boolean
     */
    public function getAppId($storeId = null)
    {
        $appId = trim($this->getConfigValue(self::CLIENT_ID, $storeId));
        return $appId;
    }

    /**
     * For get prompt cancle function
     *
     * @param int $storeId
     * @return boolean
     */
    public function getPromptCancle($storeId = null)
    {
        return $this->getConfigValue(self::CLOSE_PROMPT, $storeId);
    }

    /**
     * For get app secret function
     *
     * @param int $storeId
     * @return boolean
     */
    public function getAppSecret($storeId = null)
    {
        $appSecret = trim($this->getConfigValue(self::CLIENT_SECRATE, $storeId));
       
        return $appSecret;
    }

    /**
     * For get auth url function
     *
     * @param string $type
     * @return $authUrl
     */
    public function getAuthUrl($type)
    {
        $authUrl = $this->getBaseAuthUrl();
        $type = 'Google';
        $param = 'hauth.done=' . $type;
        if ($type === 'Live') {
            return $authUrl . $param;
        }
       
        return $authUrl . ($param ? (strpos($authUrl, '?') ? '&' : '?') . $param : '');
    }
}
