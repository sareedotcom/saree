<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources;

use Potato\Zendesk\Lib\Zendesk\API\HttpClient;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Chat\Apps;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Chat\Integrations;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\ChainedParametersTrait;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * This class serves as a container to allow calls to $this->client->chat
 *
 * @method Apps apps()
 */
class Chat
{
    use ChainedParametersTrait;
    use InstantiatorTrait;

    public $client;

    /**
     * Sets the client to be used
     *
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public static function getValidSubResources()
    {
        return [
            'apps' => Apps::class,
            'integrations' => Integrations::class,
        ];
    }
}
