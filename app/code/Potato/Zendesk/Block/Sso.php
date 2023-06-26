<?php
namespace Potato\Zendesk\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Potato\Zendesk\Model\Config;
use Potato\Zendesk\Api\SsoManagementInterface;

class Sso extends Template
{
    /** @var SsoManagementInterface */
    protected $ssoManagement;

    /** @var CustomerSession */
    protected $customerSession;

    /**
     * @param Template\Context $context
     * @param SsoManagementInterface $ssoManagement
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SsoManagementInterface $ssoManagement,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ssoManagement = $ssoManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('po_zendesk/sso/jwt');
    }

    /**
     * @return string
     */
    public function getLoginCookieName()
    {
        return Config::SSO_LOGIN_NEEDED_COOKIE_NAME;
    }

    /**
     * @return string
     */
    public function getLogoutCookieName()
    {
        return Config::SSO_LOGOUT_NEEDED_COOKIE_NAME;
    }

    /**
     * @return string
     */
    public function getLoginJwtIframe()
    {
        $location = $this->ssoManagement->getLocationByCustomer($this->customerSession->getCustomer());
        return "<iframe src='{$location}' height='1' width='1'></iframe>";
    }

    /**
     * @return string
     */
    public function getLogoutJwtIframe()
    {
        $location = $this->ssoManagement->getJwtLogoutUrl();
        return "<iframe src='{$location}' height='1' width='1'></iframe>";
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $loginCookieValue = $this->templateContext->getRequest()->getCookie($this->getLoginCookieName(), null);
        $logoutCookieValue = $this->templateContext->getRequest()->getCookie($this->getLogoutCookieName(), null);
        if (null === $loginCookieValue && null === $logoutCookieValue) {
           return '';
        }
        return parent::_toHtml();
    }
}
