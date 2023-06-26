<?php

namespace Pektsekye\OptionDependent\Model\ResourceModel\Option;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     */
    public function _construct()
    {
        $this->_init('Pektsekye\OptionDependent\Model\Option', 'Pektsekye\OptionDependent\Model\ResourceModel\Option');
    }

}
