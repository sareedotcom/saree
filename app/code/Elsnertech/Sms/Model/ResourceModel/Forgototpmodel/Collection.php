<?php
namespace Elsnertech\Sms\Model\ResourceModel\Forgototpmodel;

/**
 * Class Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Elsnertech\Sms\Model\Forgototpmodel', 'Elsnertech\Sms\Model\ResourceModel\Forgototpmodel');
    }

    
}
