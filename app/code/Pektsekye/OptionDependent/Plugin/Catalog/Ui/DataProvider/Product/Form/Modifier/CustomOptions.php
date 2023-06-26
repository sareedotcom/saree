<?php

namespace Pektsekye\OptionDependent\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\DataType\Number;

class CustomOptions
{

    protected $_odOption;
    protected $_odValue;      
    protected $locator;
    protected $storeManager;
    
    
    public function __construct(
        \Pektsekye\OptionDependent\Model\Option $odOption,   
        \Pektsekye\OptionDependent\Model\Value $odValue,  
        \Magento\Catalog\Model\Locator\LocatorInterface $locator, 
        \Magento\Store\Model\StoreManagerInterface $storeManager             
    ) {
        $this->_odOption = $odOption; 
        $this->_odValue = $odValue; 
        $this->locator = $locator;   
        $this->storeManager = $storeManager;            
    }


    public function beforeModifyData(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject, $data)
    {
    
        $optionRowIds = [];
        $valueRowIds = [];
        $valueChildrenIds = [];
            
        $odOptions = $this->_odOption->getCollection()->addFieldToFilter('product_id', $this->locator->getProduct()->getId());		
        foreach ($odOptions as $option){			
          if ($option->getRowId() != null)
            $optionRowIds[$option->getOptionId()] = (int) $option->getRowId();				
        }
      
        $odValues = $this->_odValue->getCollection()->addFieldToFilter('product_id', $this->locator->getProduct()->getId());		
        foreach ($odValues as $value) {		
          $valueRowIds[$value->getOptionTypeId()] = (int) $value->getRowId();
          $valueChildrenIds[$value->getOptionTypeId()] = $value->getChildren();							    					
        }	


        $options = [];
        
        $productOptions = $this->locator->getProduct()->getOptions() ?: [];
        foreach ($productOptions as $index => $option) {
        
            if (isset($optionRowIds[$option->getOptionId()])){
              $options[$index]['row_id'] = $optionRowIds[$option->getOptionId()];
            }
            
            $values = $option->getValues() ?: [];
            foreach ($values as $value) {

              if (isset($valueRowIds[$value->getOptionTypeId()])){
                $options[$index][$subject::GRID_TYPE_SELECT_NAME][] = [
                  'row_id' => $valueRowIds[$value->getOptionTypeId()],
                  'product_id' => $option->getProductId(),                  
                  'children' => $valueChildrenIds[$value->getOptionTypeId()],
                ];                      
              }            

            }
        }

        $data = array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    $subject::DATA_SOURCE_DEFAULT => [
                        $subject::FIELD_ENABLE => 1,
                        $subject::GRID_OPTIONS_NAME => $options
                    ]
                ]
            ]
        );
        
        return [$data];
    }



    public function afterModifyMeta(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject, $meta)
    {

       // $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'][0] = $this->getOdIdFieldConfig(1);        
       // $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'][5] = $this->getOdChildrenFieldConfig(45);  
           
        if (isset($meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'])){

          $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'][$subject::FIELD_IS_DELETE] = $this->getIsDeleteFieldConfig(60);


          $row = $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'];
 
  //        $row = array_values($row);
       //to set correct sort order for colunm titles 
  //        array_splice($row, 0, 0, array('row_id' => $this->getOdIdFieldConfig(1)));
  //        array_splice($row, 5, 0, array('children' => $this->getOdChildrenFieldConfig(45)));


            $this->array_insert($row, 0, array('row_id' => $this->getOdIdFieldConfig(1)));   
            $this->array_insert($row, 5, array('children' => $this->getOdChildrenFieldConfig(45))); 
  
            
            $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['children']['record']['children'] = $row;
  
            $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['arguments']['data']['config']['pageSize'] = 1000;
            $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::GRID_TYPE_SELECT_NAME]['arguments']['data']['config']['pageSize'] = 1000;               
        }
        
        if (isset($meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::CONTAINER_TYPE_STATIC_NAME]['children'])){        
                
          $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::CONTAINER_TYPE_STATIC_NAME]['children']['row_id'] = $this->getOdIdFieldConfig(1, true);         
          $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children'][$subject::GRID_OPTIONS_NAME]['children']['record']['children'][$subject::CONTAINER_OPTION]['children'][$subject::CONTAINER_TYPE_STATIC_NAME]['children'][$subject::FIELD_SKU_NAME] = $this->getSkuFieldConfig(30);          
          $meta[$subject::GROUP_CUSTOM_OPTIONS_NAME]['children']['ox_js_code'] = $this->getHeaderContainerConfig(40);
        
        }
        
        return $meta;   
    }
    
 
    
    protected function getOdIdFieldConfig($sortOrder, $isOptionRowId = false)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Id'),
                        'additionalClasses' => 'od-id-column',                        
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => $isOptionRowId ? "Pektsekye_OptionDependent/form/element/input_id_label" : "Pektsekye_OptionDependent/form/element/input_id",
                        'dataScope' => 'row_id',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'rowId' => '${ $.provider }:${ $.parentScope }.row_id',
                            '__disableTmpl' => ['rowId' => false]                            
                        ]                         
                    ],
                ],
            ],
        ];
    }    



    protected function getSkuFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('SKU'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => "Pektsekye_OptionDependent/form/element/input_sku",                         
                        'dataScope' => 'sku',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'rowId' => '${ $.provider }:${ $.parentScope }.row_id',
                            '__disableTmpl' => ['rowId' => false]                            
                        ]                        
                    ],
                ],
            ],
        ];
    }


    protected function getCurrencySymbol()
    {
        return $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }
    

    protected function getOdChildrenFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Children'),
                        'additionalClasses' => 'od-children-column',                         
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => "Pektsekye_OptionDependent/form/element/input_children",                        
                        'dataScope' => 'children',
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'rowId' => '${ $.provider }:${ $.parentScope }.row_id',
                            '__disableTmpl' => ['rowId' => false]                            
                        ]                        
                    ],
                ],
            ],
        ];
    }    
    
    
    protected function getIsDeleteFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'elementTmpl' => "Pektsekye_OptionDependent/form/element/input_delete",
                        'template' => "Pektsekye_OptionDependent/form/element/input_delete",                                                 
                        'fit' => true,
                        'sortOrder' => $sortOrder
                    ],
                ],
            ],
        ];
    }    
    
    
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => "Pektsekye_OptionDependent/form/components/js",                         
                        'sortOrder' => $sortOrder,
                        'content' => '',
                        'idColumn' => 'aaa'
                    ],
                ],
            ],
        ];
    }    



    protected function array_insert(&$array, $position, $insert_array) {
      $first_array = array_splice ($array, 0, $position);
      $array = array_merge ($first_array, $insert_array, $array);
    } 


}
