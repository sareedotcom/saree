<?php

namespace Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export;

class Import extends \Pektsekye\OptionDependent\Controller\Adminhtml\Od\Export
{


  public function execute()
  {
    if ($this->getRequest()->isPost() && $this->getRequest()->getFiles('import_file')) {
        try {
            
            $importHandler = $this->_objectManager->create('Pektsekye\OptionDependent\Model\CsvImportHandler');
            $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_file'));

            $this->messageManager->addSuccess(__('Product custom options have been imported.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Invalid file upload attempt.'.$e->getMessage()));
        }
    } else {
        $this->messageManager->addError(__('Invalid file upload attempt..'));
    }
    $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
  } 

}
