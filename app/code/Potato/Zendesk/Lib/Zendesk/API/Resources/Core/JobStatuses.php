<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindMany;

/**
 * Class JobStatuses
 */
class JobStatuses extends ResourceAbstract
{
    use Find;

    use FindMany;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'job_status';
    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'job_statuses';
}
