<?php

namespace Potato\Zendesk\Api\Data;

interface TicketInterface
{
    const ID = 'id';
    const URL = 'url';
    const ORDER = 'order';
    const STATUS = 'status';
    const SUBJECT = 'subject';
    const PRIORITY = 'priority';
    const DESCRIPTION = 'description';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getUrl();
    
    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getOrder();

    /**
     * @param string $order
     * @return $this
     */
    public function setOrder($order);

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);

    /**
     * @return string
     */
    public function getPriority();

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return null|string
     */
    public function getCreatedAt();

    /**
     * @param null|string
     */
    public function getUpdatedAt();
}
