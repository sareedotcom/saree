<?php

namespace Potato\Zendesk\Model\Data;

use Potato\Zendesk\Api\Data\AttachmentInterface;
use Magento\Framework\DataObject;

class Attachment extends DataObject implements AttachmentInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(AttachmentInterface::ID);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->getData(AttachmentInterface::FILENAME);
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        return $this->setData(AttachmentInterface::FILENAME, $filename);
    }

    /**
     * @return string
     */
    public function getContentUrl()
    {
        return $this->getData(AttachmentInterface::CONTENT_URL);
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->getData(AttachmentInterface::SIZE);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getData(AttachmentInterface::TYPE);
    }
}