<?php
namespace Potato\Zendesk\Controller\Sso;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Potato\Zendesk\Api\SsoManagementInterface;

class Logout extends Action
{
    /** @var SsoManagementInterface  */
    protected $ssoManagement;

    /**
     * @param Context $context
     * @param SsoManagementInterface $ssoManagement
     */
    public function __construct(
        Context $context,
        SsoManagementInterface $ssoManagement
    ) {
        parent::__construct($context);
        $this->ssoManagement = $ssoManagement;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $url = $this->_url->getUrl('customer/account/login');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($url);
        $customerId = $this->getRequest()->getParam('external_id', null);
        if (null === $customerId) {
            return $resultRedirect;
        }
        $url = $this->getRedirectUrl($customerId);
        if (null === $url) {
            return $resultRedirect;
        }
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }

    /**
     * @param int $customerId
     *
     * @return string
     */
    protected function getRedirectUrl($customerId)
    {
        $params = [];
        if ($redirectTo = $this->getRequest()->getParam(SsoManagementInterface::ORIGIN_QUERY_RT, false)) {
            $params['_query'] = [SsoManagementInterface::MODULE_QUERY_RT => $redirectTo];
        }
        return $this->ssoManagement->getLogoutUrl($customerId, $params);
    }
}