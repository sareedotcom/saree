<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * The SharingAgreements class
 * https://developer.zendesk.com/rest_api/docs/core/sharing_agreements
 */
class SharingAgreements extends ResourceAbstract
{
    use FindAll;
}
