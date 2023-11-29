<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Filesystem;
use \Magento\Framework\Json\Helper\Data;

/**
 * Save Image Template
 */
class Upload extends Action
{
    /**
     * @var mediaBaseDirectory
     */
    public $_mediaBaseDirectory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Data
     */
    protected $jsonHelper;
    /**
     * @var StoreManager
     */
    protected $_storeManager;
    /**
     * @var Filesystem
     */
    protected $_filesystem;
    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;
    /**
     * @var fileId
     */
    protected $fileId = 'custom_upload';
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        try {
               $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
              $mediapath = $this->_mediaBaseDirectory = rtrim($mediaDir, '/');

              $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
              $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
              $uploader->setAllowRenameFiles(true);
              $path = $mediapath . '/templates/';
              $result = $uploader->save($path);
            return $this->jsonResponse($result);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @param object $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
