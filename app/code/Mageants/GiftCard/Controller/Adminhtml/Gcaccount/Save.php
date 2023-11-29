<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcaccount;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use \Mageants\GiftCard\Model\Account as Modelaccount;
use \Mageants\GiftCard\Model\Customer;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\GiftCard\Model\Templates;
use \Magento\Catalog\Model\CategoryFactory;
use \Mageants\GiftCard\Helper\Data;
use Magento\Backend\App\Action\Context;

class Save extends Action
{
    /**
     * @var Data
     */
    public $helperdata;

    /**
     * @var CategoryFactory
     */
    public $categoryfactory;

    /**
     * @var Templates
     */
    public $template;

    /**
     * @var StoreManagerInterface
     */
    public $storemanager;

    /**
     * @var Customer
     */
    public $customer;

    /**
     * @var Modelaccount
     */
    public $modelaccount;

    /**
     * @var String
     */
    protected $fileId = 'image';

    /**
     * @param Context $context
     * @param Modelaccount $modelaccount
     * @param Customer $customer
     * @param StoreManagerInterface $storemanager
     * @param Templates $template
     * @param CategoryFactory $categoryfactory
     * @param Data $helperdata
     */
    public function __construct(
        Context $context,
        Modelaccount $modelaccount,
        Customer $customer,
        StoreManagerInterface $storemanager,
        Templates $template,
        CategoryFactory $categoryfactory,
        Data $helperdata
    ) {
        $this->modelaccount =$modelaccount;
        $this->customer =$customer;
        $this->storemanager =$storemanager;
        $this->template =$template;
        $this->categoryfactory =$categoryfactory;
        $this->helperdata =$helperdata;
        parent::__construct($context);
    }

    /**
     * Perform Save action for Account
     */
    public function execute()
    {
        $data=$this->getRequest()->getPostValue();
        $urlkey=$this->getRequest()->getParam('back');
        try {
            $templateData=$this->modelaccount;
            if (isset($data['account_id'])) {
                  
                  $exist_order=$templateData->load($data['account_id']);
                  $customerid=$exist_order->getOrderId();

                  $customerdata=$this->customer;

                  $customerdata->setData($data);
                  $customerdata->setCustomerId($customerid);
                  $customerdata->save();
            }
                
                $templateData->setData($data);
                $templateData->setAccountId($data['account_id']);
                $templateData->save();
                $this->messageManager->addSuccess(__('Account data has been successfully saved.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        if ($urlkey=='edit') {
                    $emailTemplateVariables = [];
                      $exist_order=$templateData->load($data['account_id']);
                    $customerid=$exist_order->getOrderId();
            if (isset($exist_order)):
                
                $customerdata = $this->customer->load($exist_order->getOrderId());

                $emailTemplateVariables['template'] = $exist_order->getTemplate();
                if ($exist_order->getCustomUpload()) {
                    
                    $mediapath = $this->storemanager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    $emailTemplateVariables['template'] = $mediapath."giftcertificate/".$exist_order->getTemplate();
                }
                $emailTemplateVariables['left'] = '0px';
                $emailTemplateVariables['top'] = '96px';
                $emailTemplateVariables['bgcolor'] = '#f00';
                $emailTemplateVariables['color'] = '#fff';
                $emailTemplateVariables['message'] = $exist_order->getMessage();
                if ($exist_order->getComment()) {
                    $emailTemplateVariables['message'] = $exist_order->getComment();
                }
                if ($exist_order->getSendtemplateId()) {
                    
                    $templateData = $this->template->load(
                        $exist_order->getSendtemplateId()
                    );

                    if ($templateData->getPositionleft()) {
                         $emailTemplateVariables['left'] = $templateData->getPositionleft().'px';
                    }
                    if ($templateData->getPositiontop()) {
                        $emailTemplateVariables['top'] = $templateData->getPositiontop().'px';

                    }
                    if ($templateData->getColor()) {
                        $emailTemplateVariables['bgcolor'] = $templateData->getColor() ;

                    }
                    if ($templateData->getForecolor()) {
                        $emailTemplateVariables['color'] = $templateData->getForecolor() ;

                    }
                     
                }

                $_categoryFactory=$this->categoryfactory;
                 
                  $cats = explode(',', $exist_order->getCategories());
                  $categoryName='';
                foreach ($cats as $cat) {
                          $_category = $_categoryFactory->create()->load($cat);
                            
                          $categoryName.=$_category->getName().",";
                }
                $emailTemplateVariables['code'] = $exist_order->getGiftCode();
                $emailTemplateVariables['current_balance'] = $exist_order->getCurrentBalance();
                $emailTemplateVariables['sender_name'] = $customerdata->getSenderName();
                $emailTemplateVariables['sender_email'] = $customerdata->getSenderEmail();
                $emailTemplateVariables['recipient_name'] = $customerdata->getRecipientName();
                $emailTemplateVariables['category_name']=$categoryName;
                $emailTemplateVariables['recipient_email'] = $customerdata->getRecipientEmail();
                $emailTemplateVariables['validity'] = $exist_order->getExpireAt();
            endif;
                  
            if (!empty($emailTemplateVariables) && !empty($emailTemplateVariables['recipient_email'])):

                $this->helperdata->sendTemplate($emailTemplateVariables);
            
            endif;

                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
        }
       
        $this->_redirect('giftcertificate/gcaccount/index');
    }
}
