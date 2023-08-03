<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_GoogleOneTapLogin
 */
declare(strict_types=1);

namespace Elsnertech\GoogleOneTapLogin\Model;

use Exception;
use Google_Client;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;
use Elsnertech\GoogleOneTapLogin\Model\ResourceModel\GoogleOneTapLogin\CollectionFactory;

/**
 * Class GoogleOneTapLogin
 *
 * Elsnertech\GoogleOneTapLogin\Model
 */
class GoogleOneTapLogin extends AbstractModel
{
    /**
     *
     * @var emailNotificationInterface
     */
    protected $emailNotificationInterface;

    /**
     *
     * @var accountManagementInterface
     */
    protected $accountManagementInterface;

    /**
     *
     * @var random
     */
    protected $random;

    /**
     * @var apiName
     */
    protected $apiName;

    /**
     * @var apiHelper
     */
    protected $apiHelper;

    /**
     * @var collection
     */
    protected $collection;

    /**
     * @var id_token
     */
    public $id_token;

    /**
     * @var customerFactory
     */
    protected $customerFactory;

    /**
     * For __construct function
     *
     * @param Context $context
     * @param Registry $registry
     * @param CustomerFactory $customerFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param \Elsnertech\GoogleOneTapLogin\Helper\Social $apiHelper
     * @param User $userModel
     * @param DateTime $dateTime
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param CollectionFactory $collection
     * @param Random $random
     * @param AccountManagementInterface $accountManagementInterface
     * @param EmailNotificationInterface $emailNotificationInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        \Elsnertech\GoogleOneTapLogin\Helper\Social $apiHelper,
        User $userModel,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        CollectionFactory $collection,
        Random $random,
        AccountManagementInterface $accountManagementInterface,
        EmailNotificationInterface $emailNotificationInterface,
        array $data = []
    ) {
        $this->emailNotificationInterface = $emailNotificationInterface;
        $this->accountManagementInterface = $accountManagementInterface;
        $this->random = $random;
        $this->customerFactory     = $customerFactory;
        $this->customerRepository  = $customerRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->storeManager        = $storeManager;
        $this->apiHelper           = $apiHelper;
        $this->_userModel          = $userModel;
        $this->_dateTime           = $dateTime;
        $this->collection          = $collection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\GoogleOneTapLogin::class);
    }

    /**
     * For getUserProfile function
     *
     * @param string $apiName
     * @param int $id_token
     * @param string $area
     * @return array
     */
    public function getUserProfile($apiName, $id_token, $area = null)
    {
        $config = [
            'base_url'   => $this->apiHelper->getBaseAuthUrl($area),
            'providers'  => [
                $apiName => $this->getProviderData($apiName)
            ],
            'debug_mode' => true,
            'debug_file' => BP . '/var/log/googleonetaplogin.log'
        ];

        $auth = new Google_Client($config);
        
        try {
            $userProfile = $auth->verifyIdToken($id_token);
        } catch (Exception $e) {
            $auth->logoutAllProviders();
            $auth = new Google_Client($config);
            $userProfile = $auth->verifyIdToken($id_token);
        }

        return $userProfile;
    }

    /**
     * For getProviderData function
     *
     * @param string $apiName
     * @return array
     */
    public function getProviderData($apiName)
    {
        $data = [
            'enabled' => $this->apiHelper->isEnabled(),
            'keys'    => [
                'id'     => $this->apiHelper->getAppId(),
                'key'    => $this->apiHelper->getAppId(),
                'secret' => $this->apiHelper->getAppSecret()
            ]
        ];

        return array_merge($data, $this->apiHelper->getSocialConfig($apiName));
    }

    /**
     * For getCustomerBySocial function
     *
     * @param string $email
     * @param int $type
     * @return array
     */
    public function getCustomerBySocial($email, $type)
    {
        
        $customer = $this->customerFactory->create()->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem();
               
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());

        return $customer;
    }

    /**
     * For getCustomerByEmail function
     *
     * @param string $email
     * @param string $websiteId
     * @return array
     */
    public function getCustomerByEmail($email, $websiteId = null)
    {
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId ?: $this->storeManager->getWebsite()->getId());
        $customer->loadByEmail($email);

        return $customer;
    }

    /**
     * For setAuthorCustomer function
     *
     * @param string $identifier
     * @param string $email
     * @param int $customerId
     * @param int $type
     * @return void
     */
    public function setAuthorCustomer($identifier, $email, $customerId, $type)
    {
        $this->setData(
            [
                'google_id'              => $identifier,
                'customer_id'            => $customerId,
                'type'                   => $type,
                'is_send_password_email' => $this->apiHelper->canSendPassword(),
                'google_created_at'      => $this->_dateTime->date(),
                'email_id'               => $email
            ]
        )
            ->setId(null)->save();

        return $this;
    }

    /**
     * For createCustomerSocial function
     *
     * @param array $data
     * @param int $store
     * @return void
     */
    public function createCustomerSocial($data, $store)
    {
        $customer = $this->customerDataFactory->create();
        $customer->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setStoreId($store->getId())
            ->setWebsiteId($store->getWebsiteId())
            ->setCreatedIn($store->getName());
        try {
            if ($data['password'] !== null) {
                $customer = $this->customerRepository->save($customer, $data['password']);
                $this->getEmailNotification()->newAccount(
                    $customer,
                    EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
                );

            } else {

                $customer = $this->customerRepository->save($customer);
                $mathRandom        = $this->random;
                $newPasswordToken  = $mathRandom->getUniqueHash();
                $accountManagement = $this->accountManagementInterface;
                $accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);

            }
            if ($this->apiHelper->canSendPassword($store)) {

                $this->getEmailNotification()->newAccount(
                    $customer,
                    EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD
                );
            }

            $this->setAuthorCustomer($data['identifier'], $data['email'], $customer->getId(), $data['type']);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.')
            );
        } catch (Exception $e) {
            if ($customer->getId()) {
                $this->_registry->register('isSecureArea', true, true);
                $this->customerRepository->deleteById($customer->getId());
            }
            throw $e;
        }
        $customer = $this->customerFactory->create()->load($customer->getId());
        return $customer;
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     */
    private function getEmailNotification()
    {
        return $this->emailNotificationInterface;
    }
}
