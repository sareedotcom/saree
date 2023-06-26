<?php
namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;

/**
 * The Triggers class exposes field management methods for triggers
 */
class Triggers extends ResourceAbstract
{
    use Defaults;

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        $this->setRoute('findActive', "{$this->resourceName}/active.json");
    }

    /**
     * Finds all active triggers
     *
     * @param array $params
     *
     * @return \stdClass | null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\RouteException
     */
    public function findActive($params = [])
    {
        return $this->client->get($this->getRoute(__FUNCTION__), $params);
    }
}
