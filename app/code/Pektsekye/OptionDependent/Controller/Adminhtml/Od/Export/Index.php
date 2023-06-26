<?php

namespace Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export;

class Index extends \Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export
{


  public function execute()
  { 
      $this->_view->loadLayout();
      $this->_setActiveMenu('Pektsekye_OptionDependent::od_export')
          ->_addBreadcrumb(
              __('Catalog'),
              __('Catalog'))
          ->_addBreadcrumb(
              __('Dependent Product Options'),
              __('Dependent Product Options')
      );    
      $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Dependent Product Options'));        
      $this->_view->renderLayout();
  } 

}
