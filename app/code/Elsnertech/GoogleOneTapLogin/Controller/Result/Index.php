<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Controller\Result;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

/**
 * Class Index
 *
 * Elsnertech\GoogleOneTapLogin\Controller\Result
 */
class Index extends AbstractSocial
{
    
    /**
     * For execute function
     *
     * @return void
     */
    public function execute()
    {
        
        if ($this->checkCustomerLogin() && $this->session->isLoggedIn()) {
            $this->_redirect('customer/account');

            return;
        }

        $token = $this->getRequest()->getParams();
        $id_token =$token['credential'];
        
        $type = 'Google';

        try {

            $userProfile = $this->apiObject->getUserProfile($type, $id_token);
           
        } catch (LocalizedException $e) {
            $this->setBodyResponse($e->getMessage());

            return;
        }
   
        $customer     = $this->apiObject->getCustomerBySocial($userProfile['email'], $type);

        if (!$customer->getId()) {

            $customer = $this->createCustomerProcess($userProfile, $type);
        
        }
          $this->refresh($customer);
    }

    /**
     * For setBodyResponse function
     *
     * @param string $message
     * @return string
     */
    protected function setBodyResponse($message)
    {
        $content = '<html><head></head><body>';
        $content .= '<div class="message message-error">' . __('Ooophs, we got an error: %1', $message) . '</div>';
        $content .= <<<Style
<style type="text/css">
    .message{
        background: #fffbbb;
        border: none;
        border-radius: 0;
        color: #333333;
        font-size: 1.4rem;
        margin: 0 0 10px;
        padding: 1.8rem 4rem 1.8rem 1.8rem;
        position: relative;
        text-shadow: none;
    }
    .message-error{
        background:#ffcccc;
    }
</style>
Style;
        $content .= '</body></html>';
        $this->getResponse()->setBody($content);
    }

    /**
     * For checkCustomerLogin function
     *
     * @return bool
     */
    public function checkCustomerLogin()
    {
        return true;
    }
}
