<?php
 
namespace Elsnertech\Customization\Plugin;
 
class GalleryPlugin
{    
    
    public function afterGetGalleryImagesCollection(\MagicToolbox\MagicZoomPlus\Block\Product\View\Gallery $collection)
    {
        $images[$id] = $collection;
        foreach ($images[$id] as $image) {
            // @var \Magento\Framework\DataObject $image //

            $mediaType = $image->getMediaType();
            if ($mediaType != 'image' && $mediaType != 'external-video') {
                continue;
            }
            /* $img = $this->_imageHelper
                ->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                ->setImageFile($image->getFile())
                ->getUrl();*/
            $img = $image['url'];
                // echo '<pre>';print_r($image);exit;
            $iPath = $image->getPath();
            if (!is_file($iPath)) {
                if (strpos($img, $baseMediaUrl) === 0) {
                    $iPath = str_replace($baseMediaUrl, '', $img);
                    $iPath = $this->magicToolboxHelper->getMediaDirectory()->getAbsolutePath($iPath);
                } else {
                    $iPath = str_replace($baseStaticUrl, '', $img);
                    $iPath = $this->magicToolboxHelper->getStaticDirectory()->getAbsolutePath($iPath);
                }
            }
            try {
                $originalSizeArray = getimagesize($iPath);
            } catch (\Exception $exception) {
                $originalSizeArray = [0, 0];
            }

            if ($mediaType == 'image') {
                if ($makeSquareImages) {
                    $bigImageSize = ($originalSizeArray[0] > $originalSizeArray[1]) ? $originalSizeArray[0] : $originalSizeArray[1];
                    /*$img = $this->_imageHelper
                        ->init($product, 'product_page_image_large')
                        ->setImageFile($image->getFile())
                        ->keepFrame(true)
                        ->resize($bigImageSize)
                        ->getUrl();*/
                        $img = $image['url'];
                }
                $image->setData('large_image_url', $img);

                list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb', $originalSizeArray);
                $this->_imageHelper
                    ->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                    ->setImageFile($image->getFile());
                if ($makeSquareImages) {
                    $this->_imageHelper->keepFrame(true);
                }
                // $medium = $this->_imageHelper->getUrl();
                $medium = $image['url'];
                $image->setData('medium_image_url', $medium);
            }

            list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('selector', $originalSizeArray);
            $this->_imageHelper
                ->init($product, 'product_page_image_small', ['width' => $w, 'height' => $h])
                ->setImageFile($image->getFile());
            if ($makeSquareImages) {
                $this->_imageHelper->keepFrame(true);
            }
            // $thumb = $this->_imageHelper->getUrl();
            $thumb = $image['url'];
            $image->setData('small_image_url', $thumb);
        }
    }
    
}
?>
