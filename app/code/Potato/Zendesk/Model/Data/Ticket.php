<?php

namespace Potato\Zendesk\Model\Data;

use Potato\Zendesk\Api\Data\TicketInterface;
use Magento\Framework\DataObject;

class Ticket extends DataObject implements TicketInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(TicketInterface::ID);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(TicketInterface::URL);
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->getData(TicketInterface::ORDER);
    }

    /**
     * @param string $order
     * @return $this
     */
    public function setOrder($order)
    {
        return $this->setData(TicketInterface::ORDER, $order);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(TicketInterface::STATUS);
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(TicketInterface::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->getData(TicketInterface::SUBJECT);
    }
    
    /**
     * @param string $subject
     * @return mixed
     */
    public function setSubject($subject)
    {
        return $this->setData(TicketInterface::SUBJECT, $subject);
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->getData(TicketInterface::PRIORITY);
    }

    /**
     * @param string $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        return $this->setData(TicketInterface::PRIORITY, $priority);
    }
    

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(TicketInterface::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(TicketInterface::DESCRIPTION, $description);
    }

    /**
     * @return null|string
     */
    public function getCreatedAt()
    {
        return $this->getData(TicketInterface::CREATED_AT);
    }

    /**
     * @param null|string
     * @return $this
     */
    public function getUpdatedAt()
    {
        return $this->getData(TicketInterface::UPDATED_AT);
    }
}