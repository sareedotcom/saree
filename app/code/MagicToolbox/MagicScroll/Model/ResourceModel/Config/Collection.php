<?php

namespace MagicToolbox\MagicScroll\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MagicToolbox\MagicScroll\Model\Config', 'MagicToolbox\MagicScroll\Model\ResourceModel\Config');
    }
}
