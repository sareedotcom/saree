<?php

namespace Elsner\Productposition\Model\ResourceModel\Productposition;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Elsner\Productposition\Model\Productposition', 'Elsner\Productposition\Model\ResourceModel\Productposition');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>