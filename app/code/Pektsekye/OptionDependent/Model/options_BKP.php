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
        // if("PSAED1900" == $product->getSku()){
        // if("CCEH1732" == $product->getSku()){
        // if("CCDJ2256" == $product->getSku()){
        $row['product_sku'] = $product->getSku();
        $blouseasshowninImage = [];
        $blouseasshowninImage['row_id'] = "";
        $blouseArr = [];
        $blouseArr['row_id'] = "";
        $petticoatArr = [];
        $petticoatArr['row_id'] = "";
        $lehengaArr = [];
        $lehengaArr['row_id'] = "";
        $lastRowId = 0;
        $plusTwo = 0;
        $options = $this->_option->getProductOptionCollection($product);
        foreach ($options as $option) {
          $row['option_title'] = $optionTitle =str_replace('"', '""', $option->getTitle());
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



              if("Blouse as shown in Image" == $optionTitle){
                $blouseasshowninImage['row_id'] =  $blouseasshowninImage['row_id'].",".$row['row_id'];
                $blouseasshowninImage['price'] = $value->getPrice();
                $row['option_sort_order'] = $option->getSortOrder()+1;
              }
              else if("Blouse Stitching" == $optionTitle){
                $blouseArr['row_id'] = $od_values[$value->getOptionTypeId()][0];
                $blouseArr['price'] = $value->getPrice();
                $row['option_sort_order'] = $option->getSortOrder()+1;
              }
              else if("Petticoat Stitching" == $optionTitle){
                $petticoatArr['row_id'] = $od_values[$value->getOptionTypeId()][0];
                $petticoatArr['price'] = $value->getPrice();
                $row['option_sort_order'] = $option->getSortOrder()+1;
              }
              else if("Lehenga Stitching" == $optionTitle){
                $lehengaArr['row_id'] = $od_values[$value->getOptionTypeId()][0];
                $lehengaArr['price'] = $value->getPrice();
                $row['option_sort_order'] = $option->getSortOrder()+1;
              }

              if(count($blouseasshowninImage) > 1 || count($blouseArr) > 1 || count($petticoatArr) > 1 || count($lehengaArr) > 1){
                if($option->getSortOrder() == 0){
                  $plusTwo = 1;
                }
                if ($plusTwo) {
                  $row['option_sort_order'] = $option->getSortOrder()+2;  
                }
                else{
                  $row['option_sort_order'] = $option->getSortOrder()+1;
                }
              }

              if($lastRowId <  $row['row_id'])
              {
                $lastRowId = $row['row_id'];
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

        if(count($blouseasshowninImage) > 1 && count($blouseArr) > 1 && count($petticoatArr) > 1){
          
          $row['last_option_sort_order'] = $row['option_sort_order'];
          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Yes";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+1;
          $row['children'] = ltrim($blouseasshowninImage['row_id'].",".$blouseArr['row_id'].",".$petticoatArr['row_id'],",");
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Later";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+2;
          $row['children'] = ($lastRowId+4).",".($lastRowId+5).",".($lastRowId+6);
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "No";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "3";
          $row['row_id'] = $lastRowId+3;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";


          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Blouse as shown in Image";
          $row['price'] = $blouseasshowninImage['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+4;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Blouse Stitching";
          $row['price'] = $blouseArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+5;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Petticoat Stitching";
          $row['price'] = $petticoatArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "3";
          $row['row_id'] = $lastRowId+6;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";
        }
        else if(count($blouseArr) > 1 && count($petticoatArr) > 1){

          $row['last_option_sort_order'] = $row['option_sort_order'];
          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Yes";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+1;
          $row['children'] = ltrim($blouseArr['row_id'].",".$petticoatArr['row_id'],",");
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Later";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+2;
          $row['children'] = ($lastRowId+4).",".($lastRowId+5);
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "No";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "3";
          $row['row_id'] = $lastRowId+3;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Blouse Stitching";
          $row['price'] = $blouseArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+4;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Petticoat Stitching";
          $row['price'] = $petticoatArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+5;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

        }
        else if(count($blouseArr) > 1 && count($lehengaArr) > 1){

          $row['last_option_sort_order'] = $row['option_sort_order'];
          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Yes";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+1;
          $row['children'] = ltrim($blouseArr['row_id'].",".$lehengaArr['row_id'],",");
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Later";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+2;
          $row['children'] = ($lastRowId+4).",".($lastRowId+5);
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "No";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "3";
          $row['row_id'] = $lastRowId+3;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Blouse Stitching";
          $row['price'] = $blouseArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+4;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Measurements Later For";
          $row['type'] = "checkbox";
          $row['is_require'] = "1";
          $row['option_sort_order'] = $row['last_option_sort_order']+1;
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Lehenga Stitching";
          $row['price'] = $lehengaArr['price'];
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+5;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";
        }
        else if(count($lehengaArr) > 1){

          $row['last_option_sort_order'] = $row['option_sort_order'];
          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Yes";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "1";
          $row['row_id'] = $lastRowId+1;
          $row['children'] = $lehengaArr['row_id'];
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "Later";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "2";
          $row['row_id'] = $lastRowId+2;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

          $row['product_sku'] = $row['product_sku'];
          $row['option_title'] = "Custom Size";
          $row['type'] = "radio";
          $row['is_require'] = "1";
          $row['option_sort_order'] = "1";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "No";
          $row['price'] = "";
          $row['price_type'] = "fixed";
          $row['sku'] = "";
          $row['value_sort_order'] = "3";
          $row['row_id'] = $lastRowId+3;
          $row['children'] = "";
          $rowObject = new \Magento\Framework\DataObject($row);
          $csv .= $rowObject->toString($template) . "\n";

        }
        
          //Blank row
          $row['product_sku'] = "";
          $row['option_title'] = "";
          $row['type'] = "";
          $row['is_require'] = "";
          $row['option_sort_order'] = "";
          $row['max_characters'] = "";
          $row['file_extension'] =  "";
          $row['image_size_x'] =  "";
          $row['image_size_y'] =  "";
          $row['value_title'] = "";
          $row['price'] = "";
          $row['price_type'] = "";
          $row['sku'] = "";
          $row['value_sort_order'] = "";
          $row['row_id'] = "";
          $row['children'] = "";
      }  
    // }//End if $product->getSku()
      
      return $csv;    
    }    
 
 
 
    
}