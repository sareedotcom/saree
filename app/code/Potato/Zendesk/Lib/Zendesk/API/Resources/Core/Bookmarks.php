<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Create;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Delete;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * Class Bookmarks
 */
class Bookmarks extends ResourceAbstract
{
    use FindAll;
    use Create;
    use Delete;
}
