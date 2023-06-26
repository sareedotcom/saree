<?php
namespace Potato\Zendesk\Plugin\Sso;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Potato\Zendesk\Api\SsoManagementInterface;
use Potato\Zendesk\Model\Config;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Customer\Controller\Account\Login as LoginAction;

class LoginBefore
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
     * @param LoginAction $subject
     * @param \Closure $proceed
     *
     * @return Redirect
     */
    public function aroundExecute(
        LoginAction $subject,
        \Closure $proceed
    ) {
        if (!$this->config->isSsoEnabled()) {
            return $proceed();
        }
        $returnToValue = $subject->getRequest()->getParam(SsoManagementInterface::MODULE_QUERY_RT);
        if (null === $returnToValue) {
            return $proceed();
        }

        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDomain($this->customerSession->getCookieDomain())
            ->setPath($this->customerSession->getCookiePath());
        try {
            $this->cookieManager->setPublicCookie(Config::SSO_RT_COOKIE_NAME, $returnToValue, $cookieMetadata);
        } catch (\Exception $e) {}

        return $proceed();
    }
}