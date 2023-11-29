<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Index;
 
use Magento\Backend\App\Action\Context;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Codeset;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Codeset
     */
    public $codeset;

    /**
     * @var Codelist
     */
    public $codelist;

    /**
     * @param Context $context
     * @param Codelist $codelist
     * @param Codeset $codeset
     */
    public function __construct(
        Context $context,
        Codelist $codelist,
        Codeset $codeset
    ) {
        $this->codelist = $codelist;
        $this->codeset = $codeset;
        parent::__construct($context);
    }
    /**
     * Perform Delete Action
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('id')!=''):
            $id = $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();

            $row = $this->codelist->load($id);

            $codesetid=$row->getCodeSetId();

            $codesetCollection = $this->codeset->load($codesetid);

            $qty=$codesetCollection->getCodeQty();
            $unusedcode=$codesetCollection->getUnusedCode();
            $row->delete();
            $qty=$qty-1;
            $unusedcode=$unusedcode-1;

            $data['code_qty']=$qty;
            $data['unused_code']=$unusedcode;

            $codesetData=$this->codeset;

            try {
                 $codesetData->setData($data);
                      $codesetData->setCodeSetId($codesetid);
                      $codesetData->save();

            } catch (Exception $e) {
                       $this->messageManager->addError(__($e->getMessage()));
            }
            $this->messageManager->addSuccess(__('Code has been deleted '));
            $this->_redirect('giftcertificate/index/index');
        endif;
        if ($this->getRequest()->getParam('code_set_id')!=''):

            $row = $this->codeset->load($this->getRequest()->getParam('code_set_id'));

            $row->delete();

            $codelist=$this->codelist->getCollection();

            $codelist->addFieldToFilter('code_set_id', $this->getRequest()->getParam('code_set_id'));
            foreach ($codelist as $list) {
                $list->delete();
            }
            $this->messageManager->addSuccess(__('record has been deleted.'));
        endif;
            $this->_redirect($this->_redirect->getRefererUrl());
    }
}
