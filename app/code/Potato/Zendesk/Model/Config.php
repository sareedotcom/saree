<?php
namespace Potato\Zendesk\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\Framework\App\ObjectManager;

class Config
{
    const SUPPORT_ORDER_SECTION_PATH = 'potato_zendesk/features/order_section';
    const SUPPORT_CUSTOMER_SECTION_PATH = 'potato_zendesk/features/customer_section';

    const ZENDESK_CONFIG_API_AGENT_TOKEN_PATH = 'potato_zendesk/account/zendesk_token';
    const ZENDESK_CONFIG_API_AGENT_EMAIL_PATH = 'potato_zendesk/account/agent_email';
    const ZENDESK_CONFIG_API_AGENT_DOMAIN_PATH = 'potato_zendesk/account/domain';

    const ZENDESK_CONFIG_IS_ORDER_DROPDOWN_PATH = 'potato_zendesk/advanced/is_dropdown_order';
    const ZENDESK_CONFIG_IS_SUBJECT_DROPDOWN_PATH = 'potato_zendesk/advanced/is_dropdown_subject';
    const ZENDESK_CONFIG_SUBJECT_DROPDOWN_CONTENT_PATH = 'potato_zendesk/advanced/dropdown_subject_fields';
    const ZENDESK_CONFIG_API_ORDER_NUMBER_FIELD_PATH = 'potato_zendesk/features/order_number_field';

    const ZENDESK_CONFIG_API_TOKEN_PATH = 'potato_zendesk/general/token';

    const ZENDESK_CONFIG_IS_SEPARATE_WEBSITE_PATH = 'potato_zendesk/general/separate_website';
    const ZENDESK_CONFIG_IS_SEPARATE_STORE_VIEW_PATH = 'potato_zendesk/general/separate_store';

    const ZENDESK_CONFIG_SSO_ENABLED_PATH = 'potato_zendesk/sso/is_enabled';
    const ZENDESK_CONFIG_SSO_RETURN_ENABLED_PATH = 'potato_zendesk/sso/is_return_enabled';
    const ZENDESK_CONFIG_SSO_DOMAIN_PATH = 'potato_zendesk/sso/domain';
    const ZENDESK_CONFIG_SSO_SECRET_PATH = 'potato_zendesk/sso/secret';

    const SSO_LOGIN_NEEDED_COOKIE_NAME = 'po_zendesk_sso_need_login';
    const SSO_LOGOUT_NEEDED_COOKIE_NAME = 'po_zendesk_sso_need_logout';
    const SSO_RT_COOKIE_NAME = 'po_zendesk_sso_return_to';
    
    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var mixed|null  */
    protected $serializer = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            $this->serializer = ObjectManager::getInstance()
                ->get('\Magento\Framework\Serialize\Serializer\Json');
        }
    }

    /**
     * @return bool
     */
    public function isSupportOrderSection()
    {
        return (bool)$this->scopeConfig->getValue(
            self::SUPPORT_ORDER_SECTION_PATH
        );
    }

    /**
     * @return bool
     */
    public function isSupportCustomerSection()
    {
        return (bool)$this->scopeConfig->getValue(
            self::SUPPORT_CUSTOMER_SECTION_PATH
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getSubdomain($store = null)
    {
        $subdomain = $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_DOMAIN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $subdomain = str_replace(['https://', 'http://', '.zendesk.com'], '', $subdomain);
        return $subdomain;
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getAgentEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_EMAIL_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getAgentToken($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_AGENT_TOKEN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getApiTokenForStore($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Website $website
     * @return string
     */
    public function getApiTokenForWebsite($website = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
    }

    /**
     * @return string
     */
    public function getApiTokenForDefault()
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_TOKEN_PATH
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSeparateInfoForWebsite($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_SEPARATE_WEBSITE_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSeparateInfoForStoreView($store = null)
    {
        $isSeparateForWebsite = $this->isSeparateInfoForWebsite($store);
        $isSeparateForStoreView = (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_SEPARATE_STORE_VIEW_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $isSeparateForWebsite && $isSeparateForStoreView;
    }

    /**
     * @return string|null
     */
    public function getOrderNumberFieldId()
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_API_ORDER_NUMBER_FIELD_PATH
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isOrderFieldDropdown($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_ORDER_DROPDOWN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSubjectFieldDropdown($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_IS_SUBJECT_DROPDOWN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return bool
     */
    public function isSsoEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_SSO_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function isSsoReturnEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_SSO_RETURN_ENABLED_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getSsoDomain($store = null)
    {
        $subdomain = $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_SSO_DOMAIN_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $subdomain = str_replace(['https://', 'http://', '.zendesk.com'], '', $subdomain);
        return $subdomain;
    }

    /**
     * @param null|integer|Store $store
     * @return string
     */
    public function getSsoSecretShared($store = null)
    {
        return $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_SSO_SECRET_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|integer|Store $store
     * @return array
     */
    public function getSubjectDropdownContent($store = null)
    {
        $content = $this->scopeConfig->getValue(
            self::ZENDESK_CONFIG_SUBJECT_DROPDOWN_CONTENT_PATH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($this->serializer) {
            $value = $this->serializer->unserialize($content);
        } else {
            $value = unserialize($content);
        }
        $result = [];
        foreach ($value as $subjectField) {
            $result[$subjectField['tag']] = $subjectField['subject'];
        }
        return $result;
    }
}
