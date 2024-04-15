<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageants\GiftCard\Model\Giftquote;
use Mageants\GiftCard\Model\Account;
use Mageants\GiftCard\Helper\Data;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageants\GiftCard\Model\Codeset;
use Mageants\GiftCard\Model\Codelist;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Mageants\GiftCard\Model\Templates;

class SendGiftcertificateMail implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    public $productrepository;

    /**
     * @var Repository
     */
    public $_assetRepo;

    /**
     * @var Templates
     */
    public $template;

    /**
     * @var Codelist
     */
    public $_codelist;

    /**
     * @var Codeset
     */
    public $_codeset;

    /**
     * @var CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var Data
     */
    public $_helper;

    /**
     * @var Giftquote
     */
    public $_giftquote;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $registry;

    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $_account;

    /**
     * @param Giftquote $giftquote
     * @param Account $account
     * @param Data $helper
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param Codeset $codeset
     * @param Codelist $codelist
     * @param CookieManagerInterface $cookieManager
     * @param Repository $assetRepo
     * @param Templates $template
     * @param ProductRepositoryInterface $productrepository
     */
    public function __construct(
        Giftquote $giftquote,
        Account $account,
        Data $helper,
        Registry $registry,
        StoreManagerInterface $storeManager,
        Codeset $codeset,
        Codelist $codelist,
        CookieManagerInterface $cookieManager,
        Repository $assetRepo,
        Templates $template,
        ProductRepositoryInterface $productrepository
    ) {

        $this->_giftquote=$giftquote;
        $this->_account = $account;
        $this->_helper=$helper;
        $this->_storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->_codeset=$codeset;
        $this->_codelist=$codelist;
        $this->registry = $registry;
        $this->template = $template;
        $this->_assetRepo = $assetRepo;
        $this->productrepository = $productrepository;
    }
    /**
     * Send the mail Gift certificate
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer) // @codingStandardsIgnoreLine
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        $items = $order->getAllVisibleItems();
        $order_id = $order->getIncrementId();
        if ($order_id) {
            $gift_quotes= $this->_giftquote->getCollection()->addFieldToFilter('order_increment_id', $order_id);
        }
        $quote_id = [];
        foreach ($items as $item) {
            if ($item->getProductType()=='giftcertificate'):

                foreach ($gift_quotes as $gift) {
                    $validDate=null;
                    $validity = $gift->getCodeValidity();
                    if ($validity) {
                        if ($validity!==''):
                            if ($gift->getDateOfDelivery()==''):
                                $validDate = date('Y-m-d', strtotime($validity.' days'));
                            else:
                                $validDate = date('Y-m-d', strtotime(
                                    $gift->getDateOfDelivery(). ' + '.$validity.' days'
                                ));
                            endif;
                        endif;
                    }
                    if ($gift->getProductId()==$item->getProductId()):
                        $quote_id[]=$gift->getId();
                        $id = '';
                        $codesetModel=$this->_codeset->getCollection()->addFieldToFilter(
                            'code_title',
                            trim($gift->getCodesetid())
                        );
                        foreach ($codesetModel as $codeset) {
                            $id=$codeset->getId();
                        }
                        if ((int)$id):
                            $applicableCodes = '';
                            $allocatedCodes=$this->_account->getCollection()->
                            addFieldToFilter('order_increment_id', $order_id)->getData();
                            foreach ($allocatedCodes as $code) {
                                $applicableCodes = $code['gift_code'];
                            }
                            $certificateCode=[];
                            if ($applicableCodes!=''):
                                $certificateCode[]=$applicableCodes;
                                $emailTemplateVariables['bcc']='test@giftcertificate.com';
                                if ($this->_helper->getBcc()!=''):
                                    $emailTemplateVariables['bcc']=explode(",", $this->_helper->getBcc());
                                endif;
                                $gift_template = $gift->getTemplateId();
                                if ($gift->getCustomUpload()) {
                                    $mediapath = $this->_storeManager->getStore()->
                                    getBaseUrl(
                                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                                    );
                                    $gift_template = $mediapath."giftcertificate/".$gift_template;
                                }
                                if ($gift_template=="") {
                                    $productid = $item->getProductId();
                                    $store = $this->_storeManager->getStore();
                                    $product = $this->productrepository->getById($productid);
                                    
                                    $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                                    if ($product->getImage()) {
                                        $gift_template = $mediaUrl . 'catalog/product' .$product->getImage();
                                    } else {
                                        $gift_template =  $this->_assetRepo->getUrl(
                                            'Magento_Catalog::images/product/placeholder/image.jpg'
                                        );
                                    }
                                }
                                //if ($gift->getCardTypes()!=1):
                                    $emailTemplateVariables['left'] = '0px';
                                    $emailTemplateVariables['top'] = '96px';
                                    $emailTemplateVariables['bgcolor'] = '';
                                    $emailTemplateVariables['color'] = '#fff';

                                if ($gift->getSendtemplateId()) {
                                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                    $templateData = $objectManager->get(
                                        \Mageants\GiftCard\Model\Templates::class
                                    )->load($gift->getSendtemplateId());
                                    if ($templateData) {
                                        if ($templateData->getPositionleft()) {
                                            $emailTemplateVariables['left'] = $templateData->getPositionleft().'px';
                                        }
                                        if ($templateData->getPositiontop()) {
                                            $emailTemplateVariables['top'] = $templateData->getPositiontop().'px';
                                        }
                                        if ($templateData->getColor()) {
                                            $emailTemplateVariables['bgcolor'] = $templateData->getColor();
                                        }
                                        if ($templateData->getForecolor()) {
                                            $emailTemplateVariables['color'] = $templateData->getForecolor();
                                        }
                                        if ($templateData->getMessage()) {
                                            $emailTemplateVariables['message'] = $templateData->getMessage();
                                        }
                                    }
                                }
                                    $emailTemplateVariables['template'] = $gift_template;
                                if ($gift->getMessage()) {
                                    $emailTemplateVariables['message'] = $gift->getMessage();
                                }
                                    $emailTemplateVariables['current_balance'] = $gift->getGiftCardValue();
                                    $emailTemplateVariables['sender_name'] = $gift->getSenderName();
                                    $emailTemplateVariables['sender_email'] = $gift->getSenderEmail();
                                    $emailTemplateVariables['recipient_name'] = $gift->getRecipientName();
                                    $emailTemplateVariables['recipient_email'] = $gift->getRecipientEmail();
                                    $emailTemplateVariables['validity'] = 'Unlimited';
                                    $catArray=[];
                                    $catArray=explode(',', $gift->getCategories());
                                    $categoryname="";
                                foreach ($catArray as $cat) {
                                    $categoryname .= $this->_helper->getCategoriesName($cat).",";
                                }
                                    $emailTemplateVariables['category_name'] = $categoryname;

                                if ($validDate!='0000-00-00'):
                                    $emailTemplateVariables['validity'] = $validDate;
                                endif;

                                    $emailTemplateVariables['code'] = $applicableCodes;
                                
                                if ($gift->getTimezone()==''):
                                    if ($gift->getDateOfDelivery() != ''):
                                        if (empty(
                                            $emailTemplateVariables['recipient_email']
                                        ) && empty($emailTemplateVariables['recipient_name'])) {
                                            $emailTemplateVariables['recipient_email'] = $gift->getSenderEmail();
                                            $emailTemplateVariables['recipient_name'] =$gift->getSenderName();
                                        }
                                        try {
                                            $this->_helper->sendTemplate($emailTemplateVariables);
                                        } catch (Exception $ex) {
                                            $this->_logger->addDebug($ex->getMessage());
                                        }
                                            //endif;
                                    endif;
                                endif;
                                //endif;
                            endif;
                        endif;
                    endif;
                    if (!empty($quote_id)):
                        foreach ($quote_id as $id) {
                            $quote=$this->_giftquote->load($id);
                        }
                    endif;
                }
            endif;
        }
    }
}
