<?php
 
namespace Elsnertech\Customization\Block\Product\View;

class Gallery extends \MagicToolbox\MagicZoomPlus\Block\Product\View\Gallery

{
    
    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Magento\Framework\Data\Collection
     */
    public function getGalleryImagesCollection($product = null)
    {
        static $images = [];
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $id = $product->getId();
        if (!isset($images[$id])) {
            if ($this->doReloadProduct) {
                $productRepository = \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Catalog\Model\ProductRepository::class
                );
                $product = $productRepository->getById($product->getId());
            }

            $images[$id] = $product->getMediaGalleryImages();
            if ($images[$id] instanceof \Magento\Framework\Data\Collection) {
                $baseMediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $baseStaticUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
                $makeSquareImages = $this->toolObj->params->checkValue('square-images', 'Yes');

                //NOTE: to sort by position for associated products
                $collection = $this->collectionFactory->create();
                $iterator = $images[$id]->getIterator();
                $iterator->uasort(function ($a, $b) {
                    $aPos = (int)$a->getPosition();
                    $bPos = (int)$b->getPosition();
                    if ($aPos > $bPos) {
                        return 1;
                    } elseif ($aPos < $bPos) {
                        return -1;
                    }
                    return 0;
                });
                $iterator->rewind();
                while ($iterator->valid()) {
                    $collection->addItem($iterator->current());
                    $iterator->next();
                }
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

        return $images[$id];
    }

    
    
}