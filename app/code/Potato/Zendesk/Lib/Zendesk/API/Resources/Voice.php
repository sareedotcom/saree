<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources;

use Potato\Zendesk\Lib\Zendesk\API\HttpClient;
use Potato\Zendesk\Lib\Zendesk\API\Resources\Voice\PhoneNumbers;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\ChainedParametersTrait;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * This class serves as a container to allow $this->client->helpCenter
 *
 * @method PhoneNumbers phoneNumbers()
 */
class Voice
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
            'phoneNumbers' => PhoneNumbers::class,
        ];
    }
}
