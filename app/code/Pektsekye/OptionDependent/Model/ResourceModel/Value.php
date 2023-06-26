<?php

namespace Pektsekye\OptionDependent\Model\ResourceModel;

class Value extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

  public function _construct()
  {    
    $this->_init('optiondependent_value', 'od_value_id');
  }  


	
	 public function duplicate($oldOptionId, $newOptionId, $newProductId)
    {
      $read   = $this->getConnection();			
      $write  = $this->getConnection();
			$productOptionValueTable = $this->getTable('catalog_product_option_type_value');	
				  
			$select = $read->select()
				->from($productOptionValueTable, 'option_type_id')
				->where('option_id=?', $oldOptionId);
			$oldTypeIds = $read->fetchCol($select);

			$select = $read->select()
				->from($productOptionValueTable, 'option_type_id')
				->where('option_id=?', $newOptionId);
			$newTypeIds = $read->fetchCol($select);

			foreach ($oldTypeIds as $ind => $oldTypeId) {
				$sql = 'REPLACE INTO `' . $this->getMainTable() . '` '
					 . 'SELECT NULL, ' . $newTypeIds[$ind] . ', ' . $newProductId . ', `row_id`, `children`'
					 . 'FROM `' . $this->getMainTable() . '` WHERE `option_type_id`=' . $oldTypeId;
				$write->query($sql);			
			}
	 } 
	
}