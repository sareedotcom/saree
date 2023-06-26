<?php

namespace Pektsekye\OptionDependent\Controller\Adminhtml\Product\Edit;

abstract class Option extends \Magento\Backend\App\AbstractAction
{

  protected $_odOption;
  protected $_odValue;  
  protected $_jsonEncoder;
  

  public function __construct(
      \Magento\Backend\App\Action\Context $context,   
      \Pektsekye\OptionDependent\Model\Option $odOption,
      \Pektsekye\OptionDependent\Model\Value $odValue,                      
      \Magento\Framework\Json\EncoderInterface $jsonEncoder     
  ) {
      $this->_odOption    = $odOption;
      $this->_odValue     = $odValue;         
      $this->_jsonEncoder = $jsonEncoder;              
      parent::__construct($context);
  }  

  

}
