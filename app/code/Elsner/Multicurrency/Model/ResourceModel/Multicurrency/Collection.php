<?php
namespace Elsner\Multicurrency\Model\ResourceModel\Multicurrency;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'multicurrency_id';
    
    protected function _construct()
    {
        $this->_init('Elsner\Multicurrency\Model\Multicurrency', 'Elsner\Multicurrency\Model\ResourceModel\Multicurrency');
    }
}
