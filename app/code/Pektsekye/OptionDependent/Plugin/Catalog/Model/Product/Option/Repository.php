<?php

namespace Pektsekye\OptionDependent\Plugin\Catalog\Model\Product\Option;

use Magento\Catalog\Api\Data\ProductInterface;

class Repository
{
  
    protected $_odOption;   
    
    
    public function __construct(
        \Pektsekye\OptionDependent\Model\Option $odOption
    ) {
        $this->_odOption = $odOption;
    } 


    public function aroundDuplicate(\Magento\Catalog\Model\Product\Option\Repository $subject, \Closure $proceed, $originalProduct, $duplicate)
    {    
        $result = $proceed($originalProduct, $duplicate);

        $this->_odOption->getResource()->duplicate((int) $originalProduct->getId(), (int) $duplicate->getId());
      
        return $result;
    }

}
