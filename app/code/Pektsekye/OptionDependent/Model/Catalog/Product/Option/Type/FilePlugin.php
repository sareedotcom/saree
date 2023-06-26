<?php

namespace Pektsekye\OptionDependent\Model\Catalog\Product\Option\Type;

use Magento\Framework\Exception\LocalizedException;

class FilePlugin
{

    public function aroundValidateUserValue(\Magento\Catalog\Model\Product\Option\Type\File $subject, \Closure $proceed, $values)
    {
        $subject->getOption()->setIsRequire(false);
        $subject->setSkipCheckRequiredOption(true);
        
        $proceed($values);
                    
      return $subject;  
    }


}

