<?php

namespace Pektsekye\OptionDependent\Block\Adminhtml\Product\Edit;

class Js extends \Magento\Backend\Block\Widget
{

    protected $_odOption;
    protected $_odValue;    
    protected $_coreRegistry;    
    protected $_jsonEncoder;
    
        
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Pektsekye\OptionDependent\Model\Option $odOption,   
        \Pektsekye\OptionDependent\Model\Value $odValue,         
        \Magento\Framework\Registry $coreRegistry,        
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,                                     
        array $data = array()
    ) {
        $this->_odOption       = $odOption; 
        $this->_odValue        = $odValue;             
        $this->_coreRegistry   = $coreRegistry;   
        $this->_jsonEncoder    = $jsonEncoder;                                      
        parent::__construct($context, $data);
    } 

    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
       
        return $this->getData('product');
    }
    

    public function getOptionDependent()
    {
			$config = array(array(), array());
			$product_id = $this->getProduct()->getId();
						
			$lastRowId = 0;
			$lastOptionId = 1;
			
			$oIdByRowId = array();
			$odOptionIds = array();
									
			$options = $this->_odOption->getCollection()->addFieldToFilter('product_id', $product_id);		
			foreach ($options as $option){			
			  $rowId = (int) $option->getRowId();
			  $optionId = (int) $option->getOptionId();
			  
			  if ($rowId < 1)			  
			  	continue;
			  				  
				$config[0][$optionId] = $rowId;
					
			  if ($lastRowId < $rowId)
			    $lastRowId = $rowId;
			    
			  if ($lastOptionId < $optionId)			    	
			    $lastOptionId = $optionId;
			    
			  $oIdByRowId[$rowId] = $optionId; 
			  $odOptionIds[] = $optionId;			  				
			}
			
			$values = $this->_odValue->getCollection()->joinOptionIds()->addFieldToFilter('product_id', $product_id);		
			foreach ($values as $value) {		
			  $rowId = (int) $value->getRowId();			
			  $valueId = (int) $value->getOptionTypeId();
			  $optionId = (int) $value->getOptionId();
			  
			  if ($rowId < 1)			  
			  	continue;
			  				  			  
				$config[1][$valueId][0] = $rowId;
				if ($value->getChildren() != '')
					$config[1][$rowId][1] = $value->getChildren();
					
			  if ($lastRowId < $rowId)
			    $lastRowId = $rowId;
			    
			  if ($lastOptionId < $optionId)			    	
			    $lastOptionId = $optionId;
  			    
			  $oIdByRowId[$rowId] = $optionId;
			  
			  if (!in_array($optionId, $odOptionIds))  			    
			    $odOptionIds[] = $optionId;			   				    					
			}	
			
			
			$optionIds = array();			
			foreach ((array) $this->getProduct()->getOptions() as $option){//to get correct options order
			  $optionId = (int) $option->getOptionId();			
			  if (in_array($optionId, $odOptionIds))
			    $optionIds[] = $optionId;			
			}
						
	/*		
			foreach ($options as $option){	
				if ($config[0][$option->getOptionId()] == 0){
					$config[0][$option->getOptionId()] = $lastRowId + 1;
					$lastRowId++;
			  }											
			}		
	
			foreach ($values as $value) {
			  if ($config[1][$value->getOptionTypeId()][0] == 0){
					$config[1][$value->getOptionTypeId()][0] = $lastRowId + 1;
					$lastRowId++;
			  }					  							
			}	 
			*/
	    $config[3] = $lastRowId;
	    $config[4] = $lastOptionId;	    
	    $config[5] = $optionIds;		    	    
	    $config[6] = $oIdByRowId;	    
	     	    	    
      return $this->_jsonEncoder->encode($config);
      
    }

}