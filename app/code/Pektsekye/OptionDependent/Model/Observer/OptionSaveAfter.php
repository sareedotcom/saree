<?php

namespace Pektsekye\OptionDependent\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class OptionSaveAfter implements ObserverInterface
{

  protected $_objectManager;  


  public function __construct( 
      \Magento\Framework\ObjectManagerInterface $objectManager              
  ) {        
      $this->_objectManager = $objectManager;                          
  } 
 

  public function execute(\Magento\Framework\Event\Observer $observer)
  {

		$object = $observer->getEvent()->getObject();
		$resource_name = $object->getResourceName();

		if ($resource_name == 'Magento\Catalog\Model\ResourceModel\Product\Option'){
			
        if (($object->getType() == 'field'
            || $object->getType() == 'area'
            || $object->getType() == 'file'
            || $object->getType() == 'date'
            || $object->getType() == 'date_time'
            || $object->getType() == 'time')
			  && $object->getRowId()) {
			  
				$model = $this->_objectManager->create('Pektsekye\OptionDependent\Model\Option');
				$collection = $model->getCollection()->addFieldToFilter('option_id', $object->getId());
				if (count($collection) == 1){
					$item = $collection->getFirstItem();			
					$model->setId($item['od_option_id']);
	      } else {
				  $model->setId(null);			
			  }	
				$model->setOptionId($object->getId());	
				$model->setProductId($object->getProductId());			
				$model->setRowId($object->getRowId());
				$model->save();
				
			}
			
		} elseif ($resource_name == 'Magento\Catalog\Model\ResourceModel\Product\Option\Value'){

			$model = $this->_objectManager->create('Pektsekye\OptionDependent\Model\Value');
			$collection = $model->getCollection()->addFieldToFilter('option_type_id', $object->getId());
			if (count($collection) == 1){
				$item = $collection->getFirstItem();			
				$model->setId($item['od_value_id']);
			}	else {
				$model->setId(null);			
			}						
			$model->setOptionTypeId($object->getId());
			if ($object->getProduct())				
				$model->setProductId($object->getProduct()->getId());		
			else 
				$model->setProductId($object->getOption()->getProductId());			
			$model->setRowId($object->getRowId());
			$model->setChildren($object->getChildren());			
			$model->save();

		}

    return $this;
  }
  
  
}
