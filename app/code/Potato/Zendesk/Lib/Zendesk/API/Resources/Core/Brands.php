<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\MultipartUpload;

/**
 * The Brands class exposes methods as detailed on
 * https://developer.zendesk.com/rest_api/docs/core/brands
 *
 * @package Potato\Zendesk\Lib\Zendesk\API
 */
class Brands extends ResourceAbstract
{
    use Defaults;
    use MultipartUpload;

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        $this->setRoutes([
            'checkHostMapping' => "{$this->resourceName}/check_host_mapping.json",
            'updateImage'      => "{$this->resourceName}/{id}.json",
        ]);
    }

    /**
     * Check host mapping validity
     *
     * @param array $params
     *
     * @return \stdClass | null
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\RouteException
     */
    public function checkHostMapping(array $params = [])
    {
        return $this->client->get($this->getRoute(__FUNCTION__), $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadName()
    {
        return 'brand[photo][uploaded_data]';
    }

    /**
     * {$@inheritdoc}
     */
    public function getUploadRequestMethod()
    {
        return 'PUT';
    }

    /**
     * Update a brand's image
     *
     * @param array $params
     *
     * @return \stdClass | null
     */
    public function updateImage(array $params = [])
    {
        $this->setAdditionalRouteParams(['id' => $this->getChainedParameter(self::class)]);

        return $this->upload($params, __FUNCTION__);
    }
}
