<?php

namespace Logicrays\ReviewImage\ViewModel;

class Review implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Logicrays\ReviewImage\Model\ReviewMediaFactory
     */
    protected $reviewFactory;

    /**
     * @param  \Logicrays\ReviewImage\Model\ReviewMediaFactory  $reviewFactory
     */
    public function __construct(
        \Logicrays\ReviewImage\Model\ReviewMediaFactory $reviewFactory
    ) {
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * Gets Review Collection
     */
    public function getReviewById($reviewId)
    {
        $reviewObj = $this->reviewFactory
        ->create()
        ->getCollection()
        ->addFieldToFilter('review_id', $reviewId);
        return $reviewObj;
    }
}
