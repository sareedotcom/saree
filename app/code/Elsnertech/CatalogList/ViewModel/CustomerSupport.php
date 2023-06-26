<?php

namespace Elsnertech\CatalogList\ViewModel;

use Magento\Framework\App\Http\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session;

class CustomerSupport implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    protected $_httpContext;
    protected $_sessionManager;

    public function __construct(Context $httpContext, SessionManagerInterface $session,Session $customer) {
        $this->_httpContext = $httpContext;
        $this->customer = $customer;
        $this->_sessionManager = $session;
    }

    //Get Session For Mobile Menu session
    public function getSessionData(){
        return $this->customer;
    }
}