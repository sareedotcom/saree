<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources;

use Potato\Zendesk\Lib\Zendesk\API\HttpClient;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Embeddable\ConfigSets;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\ChainedParametersTrait;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * This class serves as a container to allow $this->client->embeddable
 *
 * @method ConfigSets configSets()
 */
class Embeddable
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
     * @inheritdoc
     * @return array
     */
    public static function getValidSubResources()
    {
        return [
            'configSets' => ConfigSets::class,
        ];
    }
}
