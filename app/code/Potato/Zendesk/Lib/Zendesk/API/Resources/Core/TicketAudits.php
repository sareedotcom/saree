<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException;
use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;

/**
 * The TicketAudits class exposes read only audit methods
 *
 * @package Potato\Zendesk\Lib\Zendesk\API
 */
class TicketAudits extends ResourceAbstract
{
    use Defaults {
        findAll as traitFindAll;
        find as traitFind;
    }
    /**
     * {@inheritdoc}
     */
    protected $objectName = 'audit';
    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'audits';

    /**
     * Declares routes to be used by this resource.
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'findAll' => 'tickets/{ticket_id}/audits.json',
            'find'    => 'tickets/{ticket_id}/audits/{id}.json',
        ]);
    }

    /**
     * Returns all audits for a particular ticket
     *
     * @param array $params
     *
     * @return \stdClass | null
     * @throws MissingParametersException
     */
    public function findAll(array $params = [])
    {
        $routeParams = $this->addChainedParametersToParams($params, ['ticket_id' => Tickets::class]);

        if (! $this->hasKeys($routeParams, ['ticket_id'])) {
            throw new MissingParametersException(__METHOD__, ['ticket_id']);
        }

        $this->setAdditionalRouteParams($routeParams);

        return $this->traitFindAll($params);
    }

    /**
     * Show a specific audit record
     *
     * @param null|int $id
     * @param array $params
     * @return null|\stdClass
     * @throws MissingParametersException
     */
    public function find($id = null, array $params = [])
    {
        if (empty($id)) {
            $id = $this->getChainedParameter(get_class($this));
        }

        $params = $this->addChainedParametersToParams(
            $params,
            [
                'ticket_id' => Tickets::class,
            ]
        );

        if (! $this->hasKeys($params, ['ticket_id'])) {
            throw new MissingParametersException(__METHOD__, ['ticket_id']);
        }

        $this->setAdditionalRouteParams(['ticket_id' => $params['ticket_id']]);

        return $this->traitFind($id);
    }
}
