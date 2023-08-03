<?php
namespace Logicrays\ReviewImage\Block\Adminhtml\Edit;

/**
 * Class Media
 *
 * @package Logicrays\ReviewImage\Block\Adminhtml\Edit
 */
class Media extends \Magento\Backend\Block\Template
{
    /**
     * @var \Logicrays\ReviewImage\Model\ReviewMediaFactory
     */
    protected $_reviewMediaFactory;

    /**
     * Media constructor
     *
     * \Magento\Backend\Block\Template\Context $context
     * @param \Logicrays\ReviewImage\Model\ReviewMediaFactory $reviewMediaFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Logicrays\ReviewImage\Model\ReviewMediaFactory $reviewMediaFactory
    ) {
        $this->_reviewMediaFactory = $reviewMediaFactory;
        $this->setTemplate("media.phtml");
        parent::__construct($context);
    }

    /**
     * function
     * get media collection for a review
     *
     * @return \Logicrays\ReviewImage\Model\ResourceModel\ReviewMedia\Collection
     */
    public function getMediaCollection()
    {
        $thisReviewMediaCollection = $this->_reviewMediaFactory->create()
            ->getCollection()
            ->addFieldToFilter('review_id', $this->getRequest()->getParam('id'));

        return $thisReviewMediaCollection;
    }

    /**
     * function
     * get review_images directory path
     *
     * @return string
     */
    public function getReviewMediaUrl()
    {
        $reviewMediaDirectoryPath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'review_images';
        return $reviewMediaDirectoryPath;
    }
}
