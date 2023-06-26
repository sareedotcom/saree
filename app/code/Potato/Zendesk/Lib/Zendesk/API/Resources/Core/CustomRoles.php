<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * Class CustomRoles
 * https://developer.zendesk.com/rest_api/docs/core/custom_roles
 */
class CustomRoles extends ResourceAbstract
{
    use FindAll;
}
