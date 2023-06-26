<?php

namespace Potato\Zendesk\Api\Data;

interface MessageInterface
{
    const ID = 'id';
    const HTML_BODY = 'html_body';
    const AUTHOR_ID = 'author_id';
    const ATTACHMENTS = 'attachments';
    const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getHtmlBody();

    /**
     * @param string $htmlBody
     * @return $this
     */
    public function setHtmlBody($htmlBody);

    /**
     * @return int
     */
    public function getAuthorId();

    /**
     * @return array
     */
    public function getAttachments();

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments($attachments);

    /**
     * @return null|string
     */
    public function getCreatedAt();
}
