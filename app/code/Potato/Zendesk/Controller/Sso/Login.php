<?php
namespace Potato\Zendesk\Controller\Sso;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Redirect;
use Potato\Zendesk\Api\SsoManagementInterface;

class Login extends Action
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->getRedirectUrl());
        return $resultRedirect;
    }

    /**
     * @return string
     */
    protected function getRedirectUrl()
    {
        $params = [];
        if ($redirectTo = $this->getRequest()->getParam(SsoManagementInterface::ORIGIN_QUERY_RT, false)) {
            $params['_query'] = [SsoManagementInterface::MODULE_QUERY_RT => $redirectTo];
        }
        return $this->_url->getUrl('customer/account/login', $params);
    }
}