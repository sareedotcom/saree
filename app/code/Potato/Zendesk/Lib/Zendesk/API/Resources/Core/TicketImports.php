<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Create;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\CreateMany;

/**
 * The TicketImport class exposes import methods for tickets
 * https://developer.zendesk.com/rest_api/docs/core/ticket_import
 */
class TicketImports extends ResourceAbstract
{
    use Create;

    use CreateMany;

    /**
     * {@inheritdoc}
     */
    protected $resourceName = 'ticket';

    /**
     * Sets up the available routes for the resource.
     */
    protected function setUpRoutes()
    {
        $this->setRoutes([
            'create'     => 'imports/tickets.json',
            'createMany' => 'imports/tickets/create_many.json',
        ]);
    }
}
