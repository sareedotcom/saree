<?php
namespace Logicrays\ReviewImage\Model\ResourceModel;

/**
 * Class ReviewMedia
 *
 * @package Logicrays\ReviewImage\Model\ResourceModel
 */
class ReviewMedia extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * constructor
     *
     */
    protected function _construct()
    {
        $this->_init('logicrays_reviewimage', 'primary_id');
    }
}
