<?php

/**
 * Gallery view block
 *
 */
namespace MagicToolbox\MagicZoomPlus\Block\Product\View;

class GalleryOriginal extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setData('mt_as_original', true);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //NOTE: for versions 2.2.x (x >=9), 2.3.x (x >=2)
        if (class_exists('\Magento\Catalog\Block\Product\View\GalleryOptions')) {
            $galleryOptions = $objectManager->get(\Magento\Catalog\Block\Product\View\GalleryOptions::class);
            $this->setData('gallery_options', $galleryOptions);
        }

        //NOTE: for versions 2.3.x (x >=2)
        $imageHelper = $objectManager->get(\Magento\Catalog\Helper\Image::class);
        $this->setData('imageHelper', $imageHelper);
    }
}
