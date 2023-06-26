<?php

namespace Potato\Zendesk\Api;

interface TicketManagementInterface
{
    /**
     * @param string $object
     * @param null|int $id
     * @param string $format
     * @return string
     */
    public function getZendeskUrl($object = '', $id = null, $format = 'old');

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer();

    /**
     * @param array $ticketData
     * @param null|integer|\Magento\Store\Model\Store $store
     * @param array $attachments
     * @return null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\CustomException
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function createTicket($ticketData, $store, $attachments = []);

    /**
     * @param array $ticketData
     * @param null|integer|\Magento\Store\Model\Store $store
     * @param array $attachments
     * @return null
     */
    public function updateTicket($ticketData, $store, $attachments = []);

    /**
     * @param int $authorId
     * @param null|integer|\Magento\Store\Model\Store $store
     * @return \Potato\Zendesk\Api\Data\UserInterface
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getUserByAuthorId($authorId, $store);

    /**
     * @param int $ticketId
     * @param null|integer|\Magento\Store\Model\Store $store
     * @return \Potato\Zendesk\Api\Data\TicketInterface
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function getTicketById($ticketId, $store);

    /**
     * @param int $ticketId
     * @param null|integer|\Magento\Store\Model\Store $store
     * @return null|\Potato\Zendesk\Api\Data\MessageInterface[]
     */
    public function getMessageListByTicketId($ticketId, $store);

    /**
     * @param string $customerEmail
     * @param null|integer|\Magento\Store\Model\Store $store
     * @return null|\Potato\Zendesk\Api\Data\TicketInterface[]
     */
    public function getTicketListByCustomerEmail($customerEmail, $store = null);

    /**
     * @param int $customerId
     * @param null|integer|\Magento\Store\Model\Store $store
     * @return null|\Potato\Zendesk\Api\Data\TicketInterface[]
     */
    public function getTicketListByCustomerId($customerId, $store = null);
}
