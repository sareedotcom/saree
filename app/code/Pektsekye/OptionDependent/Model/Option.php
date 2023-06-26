<?php

namespace Pektsekye\OptionDependent\Model;

class Option extends \Magento\Framework\Model\AbstractModel
{    

    protected $_value;
    protected $_productFactory;    
    protected $_option;  
    
    public function __construct(
        \Pektsekye\OptionDependent\Model\Value $value,  
        \Magento\Catalog\Model\ProductFactory $productFactory,     
        \Magento\Catalog\Model\Product\Option $option,           
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry, 
        \Pektsekye\OptionDependent\Model\ResourceModel\Option $resource,                               
        \Pektsekye\OptionDependent\Model\ResourceModel\Option\Collection $resourceCollection,                 
        array $data = array()
    ) { 
        $this->_value = $value;    
        $this->_productFactory = $productFactory;   
        $this->_option = $option;                          
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

	
    public function _construct()
    {
      $this->_init('Pektsekye\OptionDependent\Model\ResourceModel\Option');        
    }

    
    public function getOptionsCsv()
    {

      $headers = new \Magento\Framework\DataObject(array(
        'product_sku' => 'product_sku',
        'option_title' => 'option_title',
        'type' => 'type',
        'is_require' => 'is_require',
        'option_sort_order' => 'option_sort_order',
        'max_characters' => 'max_characters',
        'file_extension' => 'file_extension',
        'image_size_x' => 'image_size_x',
        'image_size_y' => 'image_size_y',
        'value_title' => 'value_title',
        'price' => 'price',
        'price_type' => 'price_type',
        'sku' => 'sku',
        'value_sort_order' => 'value_sort_order',
        'row_id' => 'row_id',
        'children' => 'children'
      ));
        
      $template = '"{{product_sku}}","{{option_title}}","{{type}}","{{is_require}}","{{option_sort_order}}","{{max_characters}}","{{file_extension}}","{{image_size_x}}","{{image_size_y}}","{{value_title}}","{{price}}","{{price_type}}","{{sku}}","{{value_sort_order}}","{{row_id}}","{{children}}"';
      
      $csv = $headers->toString($template) . "\n";          
      
      $od_option_row_ids = array();
      $options = $this->getCollection();
      foreach ($options as $option)
        $od_option_row_ids[$option->getOptionId()] = $option->getRowId();	
        
      $od_values = array();		
      $values = $this->_value->getCollection();
      foreach ($values as $value)
        $od_values[$value->getOptionTypeId()] = array($value->getRowId(), $value->getChildren());
  
      $products = $this->_productFactory->create()->getCollection()->addFieldToFilter('has_options', 1);	
      foreach ($products as $product){
        $row = array();
        $row['product_sku'] = $product->getSku();
        $options = $this->_option->getProductOptionCollection($product);
        foreach ($options as $option) {
          $row['option_title'] = str_replace('"', '""', $option->getTitle());
          $row['type'] = $option->getType();
          $row['is_require'] = $option->getIsRequire();
          $row['option_sort_order'] = $option->getSortOrder();
          $row['max_characters'] = $option->getMaxCharacters();
          $row['file_extension'] = $option->getFileExtension();
          $row['image_size_x'] = $option->getImageSizeX();
          $row['image_size_y'] = $option->getImageSizeY();
          
           if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
        
            foreach ((array)$option->getValues() as $value) {
               $row['value_title'] = str_replace('"', '""', $value->getTitle());
               $row['price'] =$value->getPrice();
               $row['price_type'] = $value->getPriceType();
               $row['sku'] = str_replace('"', '""', $value->getSku());
               $row['value_sort_order'] = $value->getSortOrder();
              if (isset($od_values[$value->getOptionTypeId()])){ 
                $row['row_id'] = $od_values[$value->getOptionTypeId()][0];	
                $row['children'] = $od_values[$value->getOptionTypeId()][1];								
              }	else {								
                $row['row_id'] = '';
                $row['children'] = '';														
              }
              
              $rowObject = new \Magento\Framework\DataObject($row);
              $csv .= $rowObject->toString($template) . "\n";
                					
            }
            
          } else {
  
            $row['value_title'] = '';	
            $row['price'] = $option->getPrice();	
            $row['price_type'] = $option->getPriceType();	
            $row['sku'] = str_replace('"', '""', $option->getSku());		
            $row['value_sort_order'] = '';			
            if (isset($od_option_row_ids[$option->getOptionId()])){
              $row['row_id'] = $od_option_row_ids[$option->getOptionId()];
            } else {
              $row['row_id'] = '';
            }	
            $row['children'] = '';			
            
            $rowObject = new \Magento\Framework\DataObject($row);
            $csv .= $rowObject->toString($template) . "\n";					
          }	
        }
      }  
      
      return $csv;    
    }    
 
 
 
    
}