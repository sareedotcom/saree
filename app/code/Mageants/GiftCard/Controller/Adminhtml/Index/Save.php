<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\ResourceConnection;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Codeset;

class Save extends Action
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
     * @var ResourceConnection
     */
    public $connection;

    /**
     * @param Context $context
     * @param ResourceConnection $connection
     * @param Codelist $codelist
     * @param Codeset $codeset
     */
    public function __construct(
        Action\Context $context,
        ResourceConnection $connection,
        Codelist $codelist,
        Codeset $codeset
    ) {
        $this->connection = $connection;
        $this->codelist = $codelist;
        $this->codeset = $codeset;
        parent::__construct($context);
    }
    /**
     * Execuite save action for codeList
     */
    public function execute()
    {

        $data=$this->getRequest()->getPostValue();
        $urlkey=$this->getRequest()->getParam('back');
        $resource = $this->connection;
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('gift_code_list');
        $qty=1;
        $modelData=[];
        $codelistData=$this->codelist;
        $codesetData=$this->codeset;
        $newcount=$data['code_qty'];
        if (isset($data['code_set_id'])) {
            $codecount=$codelistData->getCollection()->addFieldToFilter('code_set_id', $data['code_set_id']);
            if ($codecount->count()>0) {
                if ($codecount->count() > $data['code_qty']) {
                    $newcount=-1;

                    $this->messageManager->addNotice(__('Qty cannot be less than current quantity'));
                    if ($urlkey=='edit') {
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        return $resultRedirect->setPath('*/*/addcodeset', ['code_set_id' =>
                            $data['code_set_id'], '_current' => true]);
                    }
                    return $this->_redirect('giftcertificate/index/index');
            
                } else {
                    $newcount=$data['code_qty']-$codecount->count();
                }
            }
        }
        $data['unused_code']=$data['code_qty'];
        try {
            $codesetData->setData($data);
            if (isset($data['code_set_id'])) {
                    $codesetData->setCodeSetId($data['code_set_id']);

            }

            $code_set_id=$codesetData->save()->getId();
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $modelData['code_set_id']=$code_set_id;
       
        while ($qty<=$newcount) {
            preg_match_ALL('#\{(.*?)\}#', trim($data['code_pattern']), $match);
            $pattern=call_user_func_array('array_merge', $match); // @codingStandardsIgnoreLine
            $count=0;
            $str='';
            $pattenrCount = count($pattern);
            for ($i=0; $i<$pattenrCount; $i++) {
                if ($pattern[$i]=='L'):
                    $pattern[$i]=chr(64+rand(0, 26)); // @codingStandardsIgnoreLine
                    $str.=$pattern[$i];
                endif;
                if ($pattern[$i]=='D'):
                    $pattern[$i]=random_int(0, 9);
                    $str.=$pattern[$i];
                endif;
            }
            $codepattern=preg_replace("/\{[^)]+\}/", $str, trim($data['code_pattern']));
            try {

                $modelData['used']=0;
              
                  $modelData['code']=$codepattern;
                  $codelistData->setData($modelData);
                  $codelistData->save();
               
            } catch (Exception $e) {
                  $this->messageManager->addError(__($e->getMessage()));
            }
            $qty++;
        }
        $this->messageManager->addSuccess(__('Codelist has been successfully saved.'));
        if ($urlkey=='edit') {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/addcodeset', ['code_set_id' => $code_set_id, '_current' => true]);
        }
        $this->_redirect('giftcertificate/index/index');
    }
}
