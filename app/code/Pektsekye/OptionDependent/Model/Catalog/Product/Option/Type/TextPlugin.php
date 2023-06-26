<?php

namespace Pektsekye\OptionDependent\Model\Catalog\Product\Option\Type;

use Magento\Framework\Exception\LocalizedException;

class TextPlugin
{

    public function aroundValidateUserValue(\Magento\Catalog\Model\Product\Option\Type\Text $subject, \Closure $proceed, $values)
    {
    
      try {
        $proceed($values);
      } catch (LocalizedException $e) {}
      
      return $subject;  
    }


}

