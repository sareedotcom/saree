<?php

namespace Potato\Zendesk\Api;

interface SsoManagementInterface
{
    const ORIGIN_QUERY_RT = 'return_to';
    const MODULE_QUERY_RT = 'po_return_to';

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     */
    public function getLocationByCustomer($customer);

    /**
     * @return string
     */
    public function getJwtLogoutUrl();

    /**
     * @param int $customerId
     * @param array $params
     * @return string|null
     */
    public function getLogoutUrl($customerId, $params = []);
}
