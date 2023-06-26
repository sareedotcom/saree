<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Embeddable;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\ResourceName;

/**
 * Abstract class for Embeddable resources
 */
abstract class ResourceAbstract extends \Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract
{
    use ResourceName;

    /**
     * @var string
     **/
    protected $prefix = 'embeddable/api/';

    /**
     * @var string
     */
    protected $apiBasePath = '';
}
