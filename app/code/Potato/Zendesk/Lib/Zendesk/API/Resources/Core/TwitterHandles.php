<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * Class TwitterHandles
 * https://developer.zendesk.com/rest_api/docs/core/monitored_twitter_handles
 */
class TwitterHandles extends ResourceAbstract
{
    const OBJ_NAME_PLURAL = 'monitored_twitter_handles';

    use Find;
    use FindAll;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'monitored_twitter_handle';
    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'monitored_twitter_handles';

    /**
     * {@inheritdoc}
     */
    protected $resourceName = 'channels/twitter/monitored_twitter_handles';
}
