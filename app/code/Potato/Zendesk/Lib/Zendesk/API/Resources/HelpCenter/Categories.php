<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\HelpCenter;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Locales;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Utility\InstantiatorTrait;

/**
 * Class Categories
 * https://developer.zendesk.com/rest_api/docs/help_center/categories
 * @method Sections sections()
 */
class Categories extends ResourceAbstract
{
    use InstantiatorTrait;
    use Defaults;
    use Locales;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'category';

    /**
     * {@inheritdoc}
     */
    public static function getValidSubResources()
    {
        return [
            'sections' => Sections::class,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function setUpRoutes()
    {
        $this->setRoute('updateSourceLocale', "{$this->resourceName}/{categoryId}/source_locale.json");
    }
}
