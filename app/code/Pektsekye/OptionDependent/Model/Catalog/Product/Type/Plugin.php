<?php

namespace Pektsekye\OptionDependent\Model\Catalog\Product\Type;

use Magento\Framework\Exception\LocalizedException;

class Plugin
{

    public function aroundCheckProductBuyState(\Magento\Catalog\Model\Product\Type\AbstractType $subject, \Closure $proceed, $product)
    {        
      try {
        $proceed($product);
      } catch (LocalizedException $e) {}
      
      return $subject;  
    }

}
