<?php

namespace Potato\Zendesk\Api\Data;

interface AttachmentInterface
{
    const ID = 'id';
    const FILENAME = 'file_name';
    const CONTENT_URL = 'content_url';
    const SIZE = 'size';
    const TYPE = 'type';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getContentUrl();

    /**
     * @return string
     */
    public function getSize();

    /**
     * @return string
     */
    public function getType();
}
