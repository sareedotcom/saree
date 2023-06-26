<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Traits\Resource;

use Potato\Zendesk\Lib\Zendesk\API\Exceptions\RouteException;

trait FindAll
{
    /**
     * List all of this resource
     *
     * @param array  $params
     *
     * @param string $routeKey
     *
     * @return \stdClass | null
     */
    public function findAll(array $params = [], $routeKey = __FUNCTION__)
    {
        try {
            $route = $this->getRoute($routeKey, $params);
        } catch (RouteException $e) {
            if (! isset($this->resourceName)) {
                $this->resourceName = $this->getResourceNameFromClass();
            }

            $route = $this->resourceName . '.json';
            $this->setRoute(__FUNCTION__, $route);
        }

        return $this->client->get(
            $route,
            $params
        );
    }
}
