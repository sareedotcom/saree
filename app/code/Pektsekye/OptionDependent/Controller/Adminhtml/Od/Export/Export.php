<?php

namespace Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export;

class Export extends \Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export
{


  public function execute()
  {
    $optionModel = $this->_objectManager->create('Pektsekye\OptionDependent\Model\Option');
    $content = $optionModel->getOptionsCsv();
    
    return $this->_fileFactory->create('product_options.csv', $content); 
                    
  }  

}
