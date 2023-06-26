<?php
namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;

/**
 * The TicketFields class exposes field management methods for tickets
 */
class TicketFields extends ResourceAbstract
{
    use Defaults;

    protected $resourceName = 'ticket_fields';
}
