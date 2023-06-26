<?php

namespace Pektsekye\OptionDependent\Model\ResourceModel\Value;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     */
    public function _construct()
    {
        $this->_init('Pektsekye\OptionDependent\Model\Value', 'Pektsekye\OptionDependent\Model\ResourceModel\Value');
    }


    public function joinOptionIds()
    {
        $this->getSelect()->joinLeft(array('cpov' => $this->getTable('catalog_product_option_type_value')),
                '`main_table`.`option_type_id` = `cpov`.`option_type_id`',
                array('option_id'));
        return $this;
    }

}
