<?php
namespace Elsner\Multicurrency\Model\ResourceModel;
 
class Multicurrency extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected $_idFieldName = 'multicurrency_id';
   
    protected function _construct()
    {
        $this->_init('elsner_multicurrency', 'multicurrency_id');
    }

}
