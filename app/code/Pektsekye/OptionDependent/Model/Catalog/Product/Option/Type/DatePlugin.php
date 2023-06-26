<?php

namespace Pektsekye\OptionDependent\Model\Catalog\Product\Option\Type;

use Magento\Framework\Exception\LocalizedException;

class DatePlugin
{

    public function aroundValidateUserValue(\Magento\Catalog\Model\Product\Option\Type\Date $subject, \Closure $proceed, $values)
    {    
      try {
        $proceed($values);
      } catch (LocalizedException $e) {}
      
      return $subject;  
    }

}

