<?php

namespace Potato\Zendesk\Model\Management;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Potato\Zendesk\Api\SsoManagementInterface;
use Potato\Zendesk\Model\Config;
use Potato\Zendesk\Lib\JWT\JWT;

class Sso implements SsoManagementInterface
{
    /** @var Config  */
    protected $config;

    /** @var JWT  */
    protected $jwt;

    /** @var CustomerRepositoryInterface  */
    protected $customerRepository;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * @param JWT $jwt
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        JWT $jwt,
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->jwt = $jwt;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     */
    public function getLocationByCustomer($customer)
    {
        $subDomain = $this->config->getSsoDomain();
        $key = $this->config->getSsoSecretShared();
        $now = time();
        $token = [
            "jti" => md5($now . rand()),
            "iat" => $now,
            "name" => $customer->getName(),
            "email" => $customer->getEmail(),
            "external_id" => $customer->getId()
        ];

        $jwt = $this->jwt->encode($token, $key);
        $location = "https://" . $subDomain . ".zendesk.com/access/jwt?jwt=" . $jwt;
        return $location;
    }

    /**
     * @return string
     */
    public function getJwtLogoutUrl()
    {
        $subDomain = $this->config->getSsoDomain();
        return "https://" . $subDomain . ".zendesk.com/access/logout";
    }

    /**
     * @param int $customerId
     * @param array $params
     *
     * @return string|null
     */
    public function getLogoutUrl($customerId, $params = [])
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $store = $this->storeManager->getStore($customer->getStoreId());
        } catch (\Exception $e) {
            return null;
        }
        return $store->getUrl('customer/account/logout', $params);
    }
}
