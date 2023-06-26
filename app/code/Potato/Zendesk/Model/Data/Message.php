<?php

namespace Potato\Zendesk\Model\Data;

use Potato\Zendesk\Api\Data\MessageInterface;
use Magento\Framework\DataObject;

class Message extends DataObject implements MessageInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(MessageInterface::ID);
    }

    /**
     * @return string
     */
    public function getHtmlBody()
    {
        return $this->getData(MessageInterface::HTML_BODY);
    }

    /**
     * @param string $htmlBody
     * @return $this
     */
    public function setHtmlBody($htmlBody)
    {
        return $this->setData(MessageInterface::HTML_BODY, $htmlBody);
    }

    /**
     * @return int
     */
    public function getAuthorId()
    {
        return $this->getData(MessageInterface::AUTHOR_ID);
    }
    
    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->getData(MessageInterface::ATTACHMENTS);
    }
    
    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        return $this->setData(MessageInterface::ATTACHMENTS, $attachments);
    }

    /**
     * @return null|string
     */
    public function getCreatedAt()
    {
        return $this->getData(MessageInterface::CREATED_AT);
    }
}