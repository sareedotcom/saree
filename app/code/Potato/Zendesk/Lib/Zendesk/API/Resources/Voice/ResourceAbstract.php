<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Voice;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\ResourceName;

/**
 * Abstract class for Voice resources
 */
abstract class ResourceAbstract extends \Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract
{
    use ResourceName;

    /**
     * @var $prefix
     **/
    protected $prefix = 'channels/voice/';
}
