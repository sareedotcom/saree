<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Controller\Result;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Elsnertech\GoogleOneTapLogin\Helper\Social as SocialHelper;
use Elsnertech\GoogleOneTapLogin\Model\GoogleOneTapLogin;
use Elsnertech\GoogleOneTapLogin\Model\GoogleOneTapLoginFactory;

/**
 * abstract Class AbstractSocial
 *
 * @package Elsnertech\GoogleOneTapLogin\Controller\Result
 */

abstract class AbstractSocial extends Action
{
    /**
     * @var _GoogleOneTapLoginFactory
     */
    protected $_GoogleOneTapLoginFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManager;

    /**
     * @var SocialHelper
     */
    protected $apiHelper;

    /**
     * @var Social
     */
    protected $apiObject;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var PhpCookieManager
     */
    protected $cookieMetadataManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * For __construct function
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManager
     * @param SocialHelper $apiHelper
     * @param GoogleOneTapLogin $apiObject
     * @param Session $customerSession
     * @param AccountRedirect $accountRedirect
     * @param RawFactory $resultRawFactory
     * @param Customer $customerModel
     * @param GoogleOneTapLoginFactory $GoogleOneTapLoginFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManager,
        SocialHelper $apiHelper,
        GoogleOneTapLogin $apiObject,
        Session $customerSession,
        AccountRedirect $accountRedirect,
        RawFactory $resultRawFactory,
        Customer $customerModel,
        GoogleOneTapLoginFactory $GoogleOneTapLoginFactory
    ) {
        $this->storeManager     = $storeManager;
        $this->accountManager   = $accountManager;
        $this->apiHelper        = $apiHelper;
        $this->apiObject        = $apiObject;
        $this->session          = $customerSession;
        $this->accountRedirect  = $accountRedirect;
        $this->resultRawFactory = $resultRawFactory;
        $this->customerModel    = $customerModel;
        $this->_GoogleOneTapLoginFactory = $GoogleOneTapLoginFactory;

        parent::__construct($context);
    }

    /**
     * Customer Collection
     *
     * @return customerid
     */
    public function collectiongetter()
    {
        $resultPage = $this->_GoogleOneTapLoginFactory->create();
         $collection = $resultPage->getCollection();

        foreach ($collection as $collection) {
            $customerid = $collection->getCustomerId();
        }
        return $customerid;
    }

    /**
     * For createCustomerProcess function
     *
     * @param string $userProfile
     * @param int $type
     * @return void
     */
    public function createCustomerProcess($userProfile, $type)
    {
        $name = explode(' ', $userProfile['azp'] ?: __('New User'));
        if (strtolower($type) === 'steam') {
            $userProfile['azp'] = trim($userProfile['azp'], "https://steamcommunity.com/openid/id/");
        }

        $user = array_merge(
            [
                'email'      => $userProfile['email'] ?: $userProfile['azp'] . '@' . strtolower($type) . '.com',
                'firstname'  => $userProfile['given_name'] ?: (array_shift($name) ?: $userProfile['azp']),
                'lastname'   => $userProfile['family_name'] ?: (array_shift($name) ?: $userProfile['azp']),
                'identifier' => $userProfile['azp'],
                'type'       => $type,
                'password'   => isset($userProfile->password) ? $userProfile->password : null
            ],
            $this->getUserData($userProfile)
        );

        return $this->createCustomer($user, $type);
    }

    /**
     * For getUserData function
     *
     * @param int $profile
     * @return array
     */
    protected function getUserData($profile)
    {
        return [];
    }

    /**
     * For createCustomer function
     *
     * @param string $user
     * @param int $type
     * @return array
     */
    public function createCustomer($user, $type)
    {
        
        $customer = $this->apiObject->getCustomerByEmail($user['email'], $this->getStore()->getWebsiteId());
        try {
                $customer = $this->apiObject->createCustomerSocial($user, $this->getStore());
        } catch (LocalizedException $e) {
                $this->emailRedirect($e->getMessage(), false);

                return false;
        }
        return $customer;
    }

    /**
     * For getStore function
     *
     * @return array
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * For emailRedirect function
     *
     * @param int $apiLabel
     * @param boolean $needTranslate
     * @return string
     */
    public function emailRedirect($apiLabel, $needTranslate = true)
    {
        $message = $needTranslate ? __('Email is Null, Please enter email in your %1 profile', $apiLabel) : $apiLabel;
        $this->messageManager->addErrorMessage($message);
        $this->_redirect('customer/account/login');

        return $this;
    }

    /**
     * For refresh function
     *
     * @param int $customer
     * @return void
     */
    public function refresh($customer)
    {
        if ($customer && $customer->getId()) {
            $this->session->setCustomerAsLoggedIn($customer);
            $this->session->regenerateId();

            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
        }
    }

    /**
     * For getCookieManager function
     *
     * @return string
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                PhpCookieManager::class
            );
        }

        return $this->cookieMetadataManager;
    }

    /**
     * For getCookieMetadataFactory function
     *
     * @return void
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                CookieMetadataFactory::class
            );
        }

        return $this->cookieMetadataFactory;
    }
}
