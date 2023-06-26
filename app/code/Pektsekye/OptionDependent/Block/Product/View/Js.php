<?php

namespace Pektsekye\OptionDependent\Block\Product\View;

class Js extends \Magento\Framework\View\Element\Template
{
    protected $_odOption;
    protected $_coreRegistry;    
    protected $_jsonEncoder;
    
                
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
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
    

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }
    
        
    public function getConfig()
    { 
			$config = array(array(), array());		
			$children = array();
			$inPreConfigured = $this->getProduct()->hasPreconfiguredValues();		
			$product_id = $this->getProduct()->getId();	
			
      foreach ($this->getProduct()->getOptions() as $option){
        $optionId = (int) $option->getOptionId();
        
        $config[0][$optionId] = array();					
        
        if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT){
        
          foreach ((array)$option->getValues() as $value){
            $vId = (int) $value->getOptionTypeId();        
            $config[1][$vId] = array(array(), array());
          } 
                 
          if ($inPreConfigured){					
            $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $optionId);	
            if (!is_null($configValue)){
              if (is_array($configValue)){
                foreach($configValue as $valueId)
                  $config[0][$optionId][] = (int) $valueId;								
              } else {
                $config[0][$optionId][] = (int) $configValue;							
              }
            }
          }								
        }
      }	
	
			
			$options = $this->_odOption->getCollection()->addFieldToFilter('product_id', $product_id);		
			foreach ($options as $option){
        $optionId = (int) $option->getOptionId();
        
			  if (!isset($config[0][$optionId]))
			    continue;	        
        			
				$option_id_by_row_id[$option->getRowId()] = $optionId;
			}
			
			$values = $this->_odValue->getCollection()->addFieldToFilter('product_id', $product_id);	
			foreach ($values as $value) {
				$valueId = (int) $value->getOptionTypeId();
				
			  if (!isset($config[1][$valueId]))
			    continue;		
			    		
				if ($value->getChildren() != ''){				
					$ids = preg_split('/\D+/', $value->getChildren(), -1, PREG_SPLIT_NO_EMPTY);
					$children[$valueId] = $ids;				
				}
				
				$value_id_by_row_id[$value->getRowId()] = $valueId;								
			}	
			
			foreach ($children as $valueId => $rowIds){
					foreach ($rowIds as $rId){
						if (isset($option_id_by_row_id[$rId])){
							$config[1][$valueId][0][] = $option_id_by_row_id[$rId];
						} elseif(isset($value_id_by_row_id[$rId])){			
							$config[1][$valueId][1][] = $value_id_by_row_id[$rId];						
						}	
					}
			}						
	
			return $this->_jsonEncoder->encode($config);
    }


    public function getInPreconfigured()
    { 			
			return $this->getProduct()->hasPreconfiguredValues() ? 'true' : 'false';
	 	}	
	
}