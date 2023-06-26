<?php
namespace Elsnertech\Customization\Plugin;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\EncoderInterface;
use Swissup\Lightboxpro\Model\Config\Source\PopupLayouts;
use Swissup\Lightboxpro\Model\Config\Source\ThumbnailsTypes;

class Galleryhelper extends \Swissup\Lightboxpro\Helper\Config
{
    /**
     * Width of thumbnails panel in advanced popup
     */
    const ADVANCED_POPUP_THUMBS_PANEL_WIDTH = 230;

    /**
     * Path to store config is zoom feature enabled
     */
    const ZOOM_ENABLED = 'lightboxpro/general/enable_zoom';

    /**
     * Path to store config is popup enabled
     */
    const POPUP_ENABLED = 'lightboxpro/general/enable_popup';

    /**
     * Path to store config thumbnails type
     */
    const THUMBNAILS_TYPE = 'lightboxpro/general/thumbnails';

    /**
     * Path to store config show image caption
     */
    const SHOW_CAPTION = 'lightboxpro/general/show_caption';

    /**
     * Path to store config main image width
     */
    const MAIN_IMG_WIDTH = 'lightboxpro/size/image_width';

    /**
     * Path to store config main image height
     */
    const MAIN_IMG_HEIGHT = 'lightboxpro/size/image_height';

    /**
     * Path to store config thumbnail width
     */
    const THUMBNAIL_WIDTH = 'lightboxpro/size/thumbnail_width';

    /**
     * Path to store config thumbnail height
     */
    const THUMBNAIL_HEIGHT = 'lightboxpro/size/thumbnail_height';

    /**
     * Path to store config thumbnail margin
     */
    const THUMBNAIL_MARGIN = 'lightboxpro/size/thumbnail_margin';

    /**
     * Path to store config thumbnail border width (active thumbnail)
     */
    const THUMBNAIL_BORDER_WIDTH = 'lightboxpro/size/thumbnail_border_width';

    /**
     * Path to store config popup layout type
     */
    const POPUP_TYPE = 'lightboxpro/popup/type';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Block\Product\View\Gallery
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * @var array
     */
    protected $galleryImagesConfig = [];

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $viewConfig
    ) {

        $this->jsonEncoder = $jsonEncoder;
        $this->imageHelper = $imageHelper;
        $this->configView = $viewConfig->getViewConfig();
        parent::__construct($context,$jsonEncoder,$imageHelper,$viewConfig);
    }

    public function init($block)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Check if zoom feature is enabled
     * @return boolean
     */
    public function zoomEnabled()
    {
        return (bool)$this->getConfig(self::ZOOM_ENABLED);
    }

    public function getPopupEnabled()
    {
        return $this->getConfig(self::POPUP_ENABLED) ? true : false;
    }

    public function showCaption()
    {
        return $this->getConfig(self::SHOW_CAPTION) ? true : false;
    }

    public function getMainImageWidth($noframe = false)
    {
        if ($width = (int)$this->getConfig(self::MAIN_IMG_WIDTH)) {
            return $width;
        }

        $type = 'product_page_image_medium_no_frame';
        if ($noframe && $width = $this->getImageAttribute($type, 'width')) {
            return $width;
        }

        return $this->getImageAttribute('product_page_image_medium', 'width');
    }

    public function getMainImageHeight($noframe = false)
    {
        if ($height = (int)$this->getConfig(self::MAIN_IMG_HEIGHT)) {
            return $height;
        }

        $type = 'product_page_image_medium_no_frame';
        if ($noframe && $height = $this->getImageAttribute($type, 'height')) {
            return $height;
        }

        return $this->getImageAttribute('product_page_image_medium', 'height');
    }

    public function getThumbnailWidth()
    {
        return (int)$this->getConfig(self::THUMBNAIL_WIDTH) ?:
            $this->getImageAttribute('product_page_image_small', 'width');
    }

    public function getThumbnailHeight()
    {
        return (int)$this->getConfig(self::THUMBNAIL_HEIGHT) ?:
            $this->getImageAttribute('product_page_image_small', 'height');
    }

    public function getThumbnailMargin()
    {
        $margin = $this->getConfig(self::THUMBNAIL_MARGIN);
        return $margin === null ? null : (int)$margin;
    }

    public function getThumbnailBorderWidth()
    {
        $width = $this->getConfig(self::THUMBNAIL_BORDER_WIDTH);
        return $width === null ? null : (int)$width;
    }

    public function getMagnifierJson()
    {
        $magnifierConfig = $this->block->getVar('magnifier') ?: [];
        $magnifierConfig['enabled'] = $this->zoomEnabled() ? 'true' : 'false';

        return $this->jsonEncoder->encode($magnifierConfig);
    }

    public function getNav()
    {
        $type = $this->getThumbnailsType();

        return $type == ThumbnailsTypes::TYPE_HIDDEN ? 'false' :
            $this->block->getVar("gallery/nav");
    }

    public function getNavDir()
    {
        $type = $this->getThumbnailsType();

        return ($type == ThumbnailsTypes::TYPE_HIDDEN ||
            $type == ThumbnailsTypes::TYPE_THEME) ?
            $this->block->getVar("gallery/navdir") : $type;
    }

    public function getThumbnailsType()
    {
        // do not read 'gallery/navdir' from this method. @see Plugin\SetGalleryOptions
        return $this->getConfig(self::THUMBNAILS_TYPE);
    }

    public function showFullscreenNav()
    {
        return ($this->getPopupLayoutType() != PopupLayouts::TYPE_SIMPLE);
    }

    public function getPopupLayoutType()
    {
        return $this->getConfig(self::POPUP_TYPE);
    }

    public function getFullscreenNavArrows()
    {
        return $this->isAdvancedPopup() ? false : (
            $this->block->getVar("gallery/fullscreen/navarrows") ?:
            $this->block->getVar("gallery/navarrows")
        );
    }

    public function getFullscreenArrows()
    {
        return $this->isAdvancedPopup() ? false : (
            $this->block->getVar("gallery/fullscreen/arrows") ?:
            $this->block->getVar("gallery/arrows")
        );
    }

    public function getFullscreenNavDir()
    {
        return $this->isAdvancedPopup() ? 'vertical' : (
            $this->block->getVar("gallery/fullscreen/navdir") ?:
            $this->block->getVar("gallery/navdir")
        );
    }

    public function isAdvancedPopup()
    {
        return $this->getPopupLayoutType() == PopupLayouts::TYPE_ADVANCED;
    }

    public function getAdvancedLayoutThumbWidth()
    {
        return self::ADVANCED_POPUP_THUMBS_PANEL_WIDTH;
    }

    /**
     * Rewritten getGalleryImages method to change image sizes
     * @param  \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Framework\Data\Collection
     */
    public function getGalleryImages(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                foreach ($this->getGalleryImagesConfig() as $configItem) {
                    $image->setData(
                        $configItem->getUrlKey(),
                        $image['url']
                        // $this->imageHelper->init(
                        //         $product,
                        //         $configItem->getImageId(),
                        //         $configItem->getAttributes()
                        //     )
                        //     ->setImageFile($image->getFile())
                        //     ->getUrl()
                    );
                }
            }
        }

        return $images;
    }

    public function getBreakpoints()
    {
        $breakpointsConfig = $this->block->getVar('breakpoints') ?: [];
        $breakpointsConfig['mobile']['options']['options']['allowfullscreen'] = true;

        return $this->jsonEncoder->encode($breakpointsConfig);
    }

    /**
     * Get gallery options
     * @return string options json
     */
    public function getOptions()
    {
        $options = [
            'nav' => $this->getNav(),
            'allowfullscreen' => $this->getPopupEnabled(),
            'showCaption' => $this->showCaption(),
            'width' => $this->getMainImageWidth(),
            'thumbwidth' => $this->getThumbnailWidth(),
            'navdir' => $this->getNavDir(),
            'thumbmargin' => $this->getThumbnailMargin(),
            'thumbborderwidth' => $this->getThumbnailBorderWidth()
        ];
         // remove all NULL and empty strings
        $options = array_filter($options, function ($v) { return $v !== null && $v !== ''; });

        if ($this->getThumbnailHeight() || $this->getThumbnailWidth()) {
            $options['thumbheight'] =  $this->getThumbnailHeight() ?: $this->getThumbnailWidth();
        }

        if ($this->getMainImageHeight() || $this->getMainImageWidth()) {
            $options['height'] = $this->getMainImageHeight() ?: $this->getMainImageWidth();
        }

        $blockVars = [
            'loop' => 'loop',
            'keyboard' => 'keyboard',
            'arrows' => 'arrows',
            'navtype' => 'navtype',
            'navarrows' => 'navarrows',
            'transitionduration' => 'transition/duration',
            'transition' => 'transition/effect',
            'thumbmargin' => 'thumbmargin',
            'thumbborderwidth' => 'thumbborderwidth'
        ];
        foreach ($blockVars as $key => $value) {
            $config = $this->block->getVar('gallery/' . $value);
            if ($config && !isset($options[$key])) {
                $options[$key] = $config;
            }
        }

        return $this->jsonEncoder->encode($options);
    }

    /**
     * Get gallery fullscreen options
     * @return string options json
     */
    public function getFullscreenOptions()
    {
        $options = [
            'nav' => $this->showFullscreenNav() ? 'thumbs' : 'false',
            'thumbwidth' => $this->isAdvancedPopup() ?
                $this->getAdvancedLayoutThumbWidth() : $this->getThumbnailWidth(),
            'navdir' => $this->getFullscreenNavDir(),
            'navarrows' => $this->getFullscreenNavArrows(),
            'arrows' => $this->getFullscreenArrows(),
            'showCaption' => $this->showCaption(),
        ];

        if (!$this->showFullscreenNav()) {
            $options['thumbheight'] = 20;
        } else if ($this->isAdvancedPopup()) {
            $options['thumbheight'] = "100%";
        }

        $blockVars = [
            'loop' => 'loop',
            'navtype' => 'navtype',
            'transitionduration' => 'transition/duration',
            'transition' => 'transition/effect'
        ];
        foreach ($blockVars as $key => $value) {
            if ($config = $this->block->getVar('gallery/fullscreen' . $value)) {
                $options[$key] = $config;
            }
        }

        return $this->jsonEncoder->encode($options);
    }

    /**
     * Find main image and return it's url
     * @param  string $imagesJson
     * @return string|bool main image url or false
     */
    public function getMainImage($imagesJson)
    {
        $imagesData = json_decode($imagesJson, true);
        if (count($imagesData) > 0){
            foreach($imagesData as $key => $value){
                if (isset($value['isMain']) && $value['isMain']) {
                    return $value['img'];
                }
            }
        }

        return false;
    }

    /**
     * Get main image label for alt tag
     * @param  string $imagesJson
     * @return string main image label or product name
     */
    public function getMainImageLabel($imagesJson)
    {
        $imagesData = json_decode($imagesJson, true);
        if (count($imagesData) > 0){
            foreach($imagesData as $key => $value){
                if (isset($value['isMain']) && $value['isMain']) {
                    return $value['caption'];
                }
            }
        }

        return '';
    }

    /**
     * Get store config by key
     * @param  string $key
     * @return mixed
     */
    protected function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $imageId
     * @param string $attributeName
     * @param string $default
     * @return string
     */
    private function getImageAttribute($imageId, $attributeName, $default = null)
    {
        $attributes = $this->configView->getMediaAttributes(
            'Magento_Catalog',
            \Magento\Catalog\Helper\Image::MEDIA_TYPE_CONFIG_NODE,
            $imageId
        );

        return isset($attributes[$attributeName]) ? $attributes[$attributeName] : $default;
    }

    /**
     * Get images config for product gallery
     *
     * @return array
     */
    public function getGalleryImagesConfig()
    {
        if (empty($this->galleryImagesConfig)) {
            $this->galleryImagesConfig = [
                new \Magento\Framework\DataObject([
                    'url_key' => 'small_image_url',
                    'image_id' => 'product_page_image_small',
                    'attributes' => [
                        'width' => $this->getThumbnailWidth(),
                        'height' => $this->getThumbnailHeight()
                    ]
                ]),
                new \Magento\Framework\DataObject([
                    'url_key' => 'medium_image_url',
                    'image_id' => 'product_page_image_medium',
                    'attributes' => [
                        'width' => $this->getMainImageWidth(true),
                        'height' => $this->getMainImageHeight(true)
                    ]
                ]),
                new \Magento\Framework\DataObject([
                    'url_key' => 'large_image_url',
                    'image_id' => 'product_page_image_large',
                    'attributes' => []
                ])
            ];
        }

        return $this->galleryImagesConfig;
    }
}
