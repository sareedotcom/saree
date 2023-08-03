<?php
namespace Logicrays\ReviewImage\Observer;

/**
 * Class ProductReviewSaveAfter
 *
 * @package Logicrays\ReviewImage\Observer
 */
class ProductReviewSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Logicrays\ReviewImage\Model\ReviewMediaFactory
     */
    protected $_reviewMediaFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;


    /**
     * ProductReviewSaveAfter constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Logicrays\ReviewImage\Model\ReviewMediaFactory $reviewMediaFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Logicrays\ReviewImage\Model\ReviewMediaFactory $reviewMediaFactory
    ) {
        $this->_request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_reviewMediaFactory = $reviewMediaFactory;
    }

    /**
     * function
     * executed after a product review is saved
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $reviewId = $observer->getEvent()->getObject()->getReviewId();
        $media = $this->_request->getFiles('review_media');
        $target = $this->_mediaDirectory->getAbsolutePath('review_images');

        if ($media) {
            try {
                for ($i = 0; $i < count($media); $i++) {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'review_media[' . $i . ']']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    $uploader->setAllowCreateFolders(true);

                    $result = $uploader->save($target);

                    $reviewMedia = $this->_reviewMediaFactory->create();
                    $reviewMedia->setMediaUrl($result['file']);
                    $reviewMedia->setReviewId($reviewId);
                    $reviewMedia->save();
                }
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->messageManager->addError("Something went wrong while saving review attachment(s).");
                }
            }
        }
    }
}
