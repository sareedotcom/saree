<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\HelpCenter;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\ResourceName;

/**
 * Abstract class for HelpCenter resources
 */
abstract class ResourceAbstract extends \Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract
{
    use ResourceName;

    /**
     * @var $prefix
     **/
    protected $prefix = 'help_center/';
}
