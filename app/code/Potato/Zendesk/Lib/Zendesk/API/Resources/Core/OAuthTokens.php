<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Delete;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * Class OAuthTokens
 */
class OAuthTokens extends ResourceAbstract
{
    use FindAll;
    use Find;
    use Delete;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'token';
    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'tokens';

    /**
     * @var string
     */
    protected $resourceName = 'oauth/tokens';

    protected function setUpRoutes()
    {
        $this->setRoute('current', "$this->resourceName/current.json");
    }

    /**
     * Wrapper for `delete`, called `revoke` in the API docs.
     *
     * @param null $id
     *
     * @return bool
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\MissingParametersException
     */
    public function revoke($id = null)
    {
        return $this->delete($id, 'delete');
    }

    /**
     * Shows the current token
     *
     * @return \stdClass | null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\RouteException
     */
    public function current()
    {
        return $this->client->get($this->getRoute(__FUNCTION__));
    }
}
