<?php

namespace Pektsekye\OptionDependent\Controller\Adminhtml\Product\Edit\Option;

class ImportDependency extends \Pektsekye\OptionDependent\Controller\Adminhtml\Product\Edit\Option
{


  public function execute()
  {   
    $data = array('options' => array(), 'values' => array());
       
    $productId = (int) $this->getRequest()->getParam('product_id');
 
    $options = $this->_odOption->getCollection()->addFieldToFilter('product_id', $productId);		
    foreach ($options as $option){
      if ($option->getRowId() > 0)			
        $data['options'][] = array('optionId' => (int) $option->getOptionId(), 'rowId' => (int) $option->getRowId());			
    }
    
    $values = $this->_odValue->getCollection()->addFieldToFilter('product_id', $productId);		
    foreach ($values as $value) {
      $children = array();
      if ($value->getChildren() != ''){		
        $ids = preg_split('/\D+/', $value->getChildren(), -1, PREG_SPLIT_NO_EMPTY);
        foreach($ids as $id){
          $children[]	= (int) $id;
        }   	
			}			
      $data['values'][] = array('valueId' => (int) $value->getOptionTypeId(), 'rowId' => (int) $value->getRowId(), 'children' => $children);	    
    }	    
   
    $this->getResponse()->setBody($this->_jsonEncoder->encode($data));    
  } 

}
