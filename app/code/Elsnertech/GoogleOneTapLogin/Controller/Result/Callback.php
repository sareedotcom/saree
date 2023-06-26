<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Controller\Result;

use Hybrid_Endpoint;

/**
 * Class Callback
 *
 * Elsnertech\GoogleOneTapLogin\Controller\Result
 */
class Callback extends AbstractSocial
{

    /**
     * For execute function
     *
     * @return void
     */
    public function execute()
    {
        $param = $this->getRequest()->getParams();

        if (isset($param['live.php'])) {
            $request = array_merge($param, ['hauth_done' => 'Live']);
        }
        if ($this->checkRequest('hauth_start', false)
            && (($this->checkRequest('error_reason', 'user_denied')
                    && $this->checkRequest('error', 'access_denied')
                    && $this->checkRequest('error_code', '200')
                    && $this->checkRequest('denied')))
        ) {
            return $this->_redirect('customer/account');
            ;
        }
        if (isset($request)) {
            Hybrid_Endpoint::process($request);
        }

        Hybrid_Endpoint::process();
    }

    /**
     * For checkRequest function
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function checkRequest($key, $value = null)
    {
        $param = $this->getRequest()->getParam($key, false);

        if ($value) {
            return $param === $value;
        }

        return $param;
    }
}
