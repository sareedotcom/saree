<?php

namespace Pektsekye\OptionDependent\Model;

class Value extends \Magento\Framework\Model\AbstractModel
{ 

    public function _construct()
    {
       $this->_init('Pektsekye\OptionDependent\Model\ResourceModel\Value');
    }
	
	    public function duplicate($oldOptionId, $newOptionId, $newProductId)
    {
		   $this->getResource()->duplicate($oldOptionId, $newOptionId, $newProductId);
	  } 


	 
}