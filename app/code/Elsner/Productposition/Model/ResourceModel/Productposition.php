<?php
namespace Elsner\Productposition\Model\ResourceModel;

class Productposition extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('productposition', 'id');
    }
}
?>