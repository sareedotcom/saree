<?php
namespace Potato\Zendesk\Plugin\Sso;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Potato\Zendesk\Api\SsoManagementInterface;
use Potato\Zendesk\Model\Config;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Controller\Account\Logout as LogoutAction;

class Logout
{
    /** @var Config */
    protected $config;

    /** @var SsoManagementInterface */
    protected $ssoManagement;

    /** @var RedirectFactory */
    protected $resultRedirectFactory;

    /** @var CookieManagerInterface  */
    protected $cookieManager;

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var CookieMetadataFactory  */
    protected $cookieMetadataFactory;

    /**
     * @param Config $config
     * @param SsoManagementInterface $ssoManagement
     * @param RedirectFactory $resultRedirectFactory
     * @param CookieManagerInterface $cookieManager
     * @param CustomerSession $customerSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Config $config,
        SsoManagementInterface $ssoManagement,
        RedirectFactory $resultRedirectFactory,
        CookieManagerInterface $cookieManager,
        CustomerSession $customerSession,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->config = $config;
        $this->ssoManagement = $ssoManagement;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cookieManager = $cookieManager;
        $this->customerSession = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param LogoutAction $subject
     * @param \Closure $proceed
     *
     * @return Redirect
     */
    public function aroundExecute(
        LogoutAction $subject,
        \Closure $proceed
    ) {
        $result = $proceed();
        if (!$this->config->isSsoEnabled()) {
            return $result;
        }
        if ($this->customerSession->isLoggedIn()) {
            return $result;
        }
        $this->createSsoLogoutNeededCookie();
        return $result;
    }

    /**
     * @return $this
     */
    private function createSsoLogoutNeededCookie()
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDomain($this->customerSession->getCookieDomain())
            ->setPath($this->customerSession->getCookiePath());
        try {
            $this->cookieManager->setPublicCookie(Config::SSO_LOGOUT_NEEDED_COOKIE_NAME, '1', $cookieMetadata);
        } catch (\Exception $e) {}
        return $this;
    }
}