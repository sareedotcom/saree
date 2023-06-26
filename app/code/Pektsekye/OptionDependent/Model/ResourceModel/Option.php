<?php

namespace Pektsekye\OptionDependent\Model\ResourceModel;

class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

  protected $_odValue;
 
  
  public function __construct(
      \Magento\Framework\Model\ResourceModel\Db\Context $resource,  
      \Pektsekye\OptionDependent\Model\Value $odValue
  ) {
      $this->_odValue = $odValue;        
      parent::__construct($resource);
  } 


  public function _construct()
  {    
    $this->_init('optiondependent_option', 'od_option_id');
  }  


  public function duplicate($oldProductId, $newProductId)
  {

    $write  = $this->getConnection();
    $read   = $this->getConnection();

    // read and prepare original product options
    $select = $read->select()
        ->from($this->getTable('catalog_product_option'), 'option_id')
        ->where('product_id=?', $oldProductId);
    $oldOptionIds = $read->fetchCol($select);

    $select = $read->select()
        ->from($this->getTable('catalog_product_option'), 'option_id')
        ->where('product_id=?', $newProductId);
    $newOptionIds = $read->fetchCol($select);
  
    foreach ($oldOptionIds as $ind => $oldOptionId) {				
        $sql = 'REPLACE INTO `' . $this->getMainTable() . '` '
            . 'SELECT NULL, ' . $newOptionIds[$ind] . ',  ' . $newProductId . ', `row_id`'
            . 'FROM `' . $this->getMainTable() . '` WHERE `option_id`=' . $oldOptionId;
        $this->getConnection()->query($sql);
    
        $this->_odValue->getResource()->duplicate($oldOptionId, $newOptionIds[$ind], $newProductId);
    }
      
  }


}