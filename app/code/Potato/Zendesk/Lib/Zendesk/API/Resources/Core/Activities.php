<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * The Activities class exposes methods for retrieving activities
 * https://developer.zendesk.com/rest_api/docs/core/activity_stream
 */
class Activities extends ResourceAbstract
{
    use Find;
    use FindAll;
}
