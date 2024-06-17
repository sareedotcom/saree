<?php
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_Base
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

declare(strict_types=1);

namespace Logicrays\Base\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Media
 * @package Logicrays\Base\Helper
 */
class Media extends Data
{
    const TEMPLATE_MEDIA_PATH = 'logicrays';

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;


    /**
     * Media constructor.
     *
     * @param Context $context
     * @param AdapterFactory $imageFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param ObjectManagerInterface $objectManager
     * @param UploaderFactory $uploaderFactory
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        AdapterFactory $imageFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        UploaderFactory $uploaderFactory,
        UrlInterface $urlInterface,
        ProductMetadataInterface $productMetadata
    ) {
        $this->imageFactory = $imageFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);

        parent::__construct($context, $objectManager, $storeManager, $urlInterface, $productMetadata);
    }

    /** Get base media url
     * 
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
    }

    /**
     * Get full media url with passed file parameter.
     * 
     * @param $file
     *
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * Get media direcoty path
     * 
     */
    public function getMediaDirectory()
    {
        return $this->mediaDirectory;
    }

     /**
     * Get base media path
     * 
     * @param string $type
     *
     * @return string
     */
    public function getBaseMediaPath($type = '')
    {
        return trim(static::TEMPLATE_MEDIA_PATH . '/' . $type, '/');
    }

    /**
     * Get media path with file name
     * 
     * @param $file
     * @param string $type
     *
     * @return string
     */
    public function getMediaPath($file, $type = '')
    {
        return $this->getBaseMediaPath($type) . '/' . $this->_prepareFile($file);
    }

    /**
     * Will prepare file for set path
     * 
     * @param string $file
     *
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * Will remove path that passed in param
     * 
     * @param $path
     *
     * @return $this
     */
    public function removePath($path)
    {
        $pathMedia = $this->mediaDirectory->getRelativePath($path);
        if ($this->mediaDirectory->isDirectory($pathMedia)) {
            $this->mediaDirectory->delete($path);
        }

        return $this;
    }

    /**
     * @param $data
     * @param string $fileName
     * @param string $type
     * @param null $oldImage
     *
     * @return $this
     */
    public function uploadImage(&$data, $fileName = 'image', $type = '', $oldImage = null)
    {
        if (isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
            if ($oldImage) {
                try {
                    $this->removeImage($oldImage, $type);
                } catch (Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }
            $data[$fileName] = '';
        } else {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileName]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);

                $path = $this->getBaseMediaPath($type);

                $image = $uploader->save(
                    $this->mediaDirectory->getAbsolutePath($path)
                );

                if ($oldImage) {
                    $this->removeImage($oldImage, $type);
                }

                $data[$fileName] = $this->_prepareFile($image['file']);
            } catch (Exception $e) {
                $data[$fileName] = isset($data[$fileName]['value']) ? $data[$fileName]['value'] : '';
            }
        }

        return $this;
    }

    /**
     * @param $file
     * @param $type
     *
     * @return $this
     */
    public function removeImage($file, $type)
    {
        $image = $this->getMediaPath($file, $type);
        if ($this->mediaDirectory->isFile($image)) {
            $this->mediaDirectory->delete($image);
        }

        return $this;
    }

    /**
     * @param $file
     * @param $size
     * @param string $type
     * @param bool $keepRatio
     *
     * @return string
     */
    public function resizeImage($file, $size, $type = '', $keepRatio = true)
    {
        $image = $this->getMediaPath($file, $type);
        if (!($imageSize = $this->correctImageSize($size))) {
            return $this->getMediaUrl($image);
        }
        list($width, $height) = $imageSize;

        $resizeImage = $this->getMediaPath($file, ($type ? $type . '/' : '') . 'resize/' . $width . 'x' . $height);

        /** @var WriteInterface $mediaDirectory */
        $mediaDirectory = $this->getMediaDirectory();
        if ($mediaDirectory->isFile($resizeImage)) {
            $image = $resizeImage;
        } elseif (!$mediaDirectory->isExist($mediaDirectory->getAbsolutePath($image))) {
            $imageResize = $this->imageFactory->create();
            $imageResize->open($mediaDirectory->getAbsolutePath($image));
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio($keepRatio);
            $imageResize->resize($width, $height);

            try {
                $imageResize->save($mediaDirectory->getAbsolutePath($resizeImage));

                $image = $resizeImage;
            } catch (Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return $this->getMediaUrl($image);
    }

    /**
     * @param $size
     *
     * @return array|bool
     */
    protected function correctImageSize($size)
    {
        if (!$size) {
            return false;
        }

        if (strpos($size, 'x') === false) {
            $width = $height = (int) $size;
        } else {
            list($width, $height) = explode('x', $size);
        }

        if (!$width && !$height) {
            return false;
        }

        return [(int) $width ?: null, (int) $height ?: null];
    }
}