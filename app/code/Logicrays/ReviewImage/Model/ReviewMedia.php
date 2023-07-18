<?php
namespace Logicrays\ReviewImage\Model;

/**
 * Class ReviewMedia
 *
 * @package Logicrays\ReviewImage\Model
 */
class ReviewMedia extends \Magento\Framework\Model\AbstractModel
{
    /**
     * constructor
     *
     */
    protected function _construct()
    {
        $this->_init('Logicrays\ReviewImage\Model\ResourceModel\ReviewMedia');
    }
}
