<?php
namespace Elsner\Productposition\Model;

class Productposition extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Elsner\Productposition\Model\ResourceModel\Productposition');
    }
}
?>