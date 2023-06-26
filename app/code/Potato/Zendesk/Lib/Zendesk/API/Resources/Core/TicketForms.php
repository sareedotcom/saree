<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException;
use Potato\Zendesk\Lib\Zendesk\API\Exceptions\ResponseException;
use Potato\Zendesk\Lib\Zendesk\API\Http;
use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;

/**
 * Class TicketForms
 */
class TicketForms extends ResourceAbstract
{
    use Defaults;

    /**
     * {@inheritdoc}
     */
    protected $resourceName = 'ticket_forms';

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'clone'   => 'ticket_forms/{id}/clone.json',
            'reorder' => 'ticket_forms/reorder.json'
        ]);
    }

    /**
     * Clones an existing ticket form (can't use 'clone' as method name)
     *
     * @param int $id
     * @return null|\stdClass
     * @throws MissingParametersException
     * @internal param array $params
     *
     */
    public function cloneForm($id = null)
    {
        $class = get_class($this);
        if (empty($id)) {
            $id = $this->getChainedParameter($class);
        }

        if (empty($id)) {
            throw new MissingParametersException(__METHOD__, ['id']);
        }

        return $this->client->post($this->getRoute('clone', ['id' => $id]));
    }

    /**
     * Reorder Ticket forms
     *
     * @param array $ticketFormIds
     *
     * @throws ResponseException
     * @throws \Exception
     * @return \stdClass | null
     */
    public function reorder(array $ticketFormIds)
    {
        $response = Http::send(
            $this->client,
            $this->getRoute(__FUNCTION__),
            ['postFields' => ['ticket_form_ids' => $ticketFormIds], 'method' => 'PUT']
        );

        return $response;
    }
}
