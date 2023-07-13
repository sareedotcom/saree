<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) Elsner Technologies Pvt. Ltd(https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Helper;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * Elsnertech\GoogleOneTapLogin\Helper
 */
class Data extends AbstractHelper
{
    /**
     *
     * @var $httpContext
     */
    private $httpContext;

    /**
     * @param Context $context
     * @param Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct($context);
        $this->httpContext = $httpContext;
    }

    /**
     * For canSendPassword function
     *
     * @param undefined $storeId
     * @return boolean
     */
    public function canSendPassword($storeId = null)
    {
        return false;
    }

    /**
     * For isCheckMode function
     *
     * @param int $storeId
     * @return boolean
     */
    public function isCheckMode($storeId = null)
    {
        return false;
    }

    /**
     * Check Customer Login
     *
     * @return customerlogin
     */
    public function isLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }
}
