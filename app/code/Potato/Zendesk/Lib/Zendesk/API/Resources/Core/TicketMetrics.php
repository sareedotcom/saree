<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * The TicketMetrics class exposes metrics methods for tickets
 */
class TicketMetrics extends ResourceAbstract
{
    use FindAll;
    use Find;

    protected $resourceName = 'ticket_metrics';

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        $this->setRoute('findAll', "{$this->resourceName}.json");
        $this->setRoute('find', "{$this->resourceName}/{id}.json");
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute($name, array $params = [])
    {
        if ('find' === $name || 'findAll' === $name) {
            $lastChained = $this->getChainedParameter(Tickets::class);

            if (! empty($lastChained)) {
                return "tickets/$lastChained/metrics.json";
            }
        }

        return parent::getRoute($name, $params);
    }
}
