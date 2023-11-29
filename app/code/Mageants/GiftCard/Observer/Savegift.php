<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Model\Giftquote;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\CookieManagerInterface ;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Save Gift class for temparary store Gift
 */
class Savegift implements ObserverInterface
{
    /**
     * @var PublicCookieMetadata
     */
    public $PublicCookieMetadata;

    /**
     * @var RequestInterface
     */
    public $_request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Mageants\GiftCard\Model\Giftquote
     */
    protected $_giftquote;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param RequestInterface $request
     * @param Session $checkoutSession
     * @param Giftquote $giftquote
     * @param Data $helper
     * @param ManagerInterface $message
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PublicCookieMetadata $PublicCookieMetadata
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        RequestInterface $request,
        Session $checkoutSession,
        Giftquote $giftquote,
        Data $helper,
        ManagerInterface $message,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        PublicCookieMetadata $PublicCookieMetadata,
        LoggerInterface $logger,
        DateTime $date,
        TimezoneInterface $timezone
    ) {
        $this->_request = $request;
        $this->_giftquote=$giftquote;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper=$helper;
        $this->_storeManager = $storeManager;
        $this->_messageManager=$message;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->logger = $logger;
        $this->PublicCookieMetadata = $PublicCookieMetadata;
        $this->date = $date;
        $this->timezone = $timezone;
    }
    
    /**
     * Execute and perform save gift for temporary
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        if ($item->getProduct()->getTypeId()=="giftcertificate") {
            $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
            $productId = $item->getProduct()->getId();
            $temp_customer_id = $this->_request->getPostValue('temp_customer_id');
            $custom_upload='';
            $custom_uploaded=false;
            if ($this->_request->getPostValue('file')) {
                $custom_upload=$this->_request->getPostValue('file');
                $custom_uploaded=true;
            } elseif ($this->_request->getPostValue('giftimage')) {
                $custom_upload = $this->_request->getPostValue('giftimage');
                $custom_uploaded=false;
            }
            $sendtemplate_id=0;
            if ($this->_request->getPostValue('template_id')) {
                $sendtemplate_id=$this->_request->getPostValue('template_id');
            }
            
            $timezone = '';
            $new_date=null;
            if ($this->_request->getPostValue('timezone')) {
                $timezone=$this->_request->getPostValue('timezone');
                $emai_time = $this->timezone->formatDateTime($timezone);
                $newtime = strtotime($emai_time)-strtotime($this->date->gmtDate());
                
                $old_date_timestamp = strtotime($emai_time);
                $new_date = date('Y-m-d H:i:s', $old_date_timestamp);
                
            }
            
            $logged_customer_id = 0000;
            if ($this->_request->getPostValue('customerid')!==null) {
                $logged_customer_id = $this->_request->getPostValue('customerid');
                $collection = $this->_giftquote->getCollection()->addFieldToFilter(
                    "product_id",
                    $productId
                )->addFieldToFilter("customer_id", $this->_request->getPostValue('customerid'));
            } else {
                $collection = $this->_giftquote->getCollection()->addFieldToFilter("product_id", $productId)
                      ->addFieldToFilter("temp_customer_id", $this->_request->getPostValue('temp_customer_id'));
            }
            if ($collection) {
                foreach ($collection as $col) {
                    $col->delete();
                }
            }
            if ($this->_request->getPostValue('manual-giftprices')!=''):
                $price = $this->_request->getPostValue('manual-giftprices');
            else:
                $price = $this->_request->getPostValue('giftprices');
            endif;
            $giftcodeset=$this->_request->getPostValue('codesetid');
            $validDate=null;
            $validity='';
            if ($item->getProduct()->getResource()->getAttributeRawValue(
                $item->getProduct()->getId(),
                'validity',
                $this->_storeManager->getStore()->
                getId()
            )!=''):
                $validity= $item->getProduct()->getResource()->getAttributeRawValue(
                    $item->getProduct()->getId(),
                    'validity',
                    $this->_storeManager->getStore()->getId()
                );
            elseif ($this->_helper->getValidity()!=''):
                $validity= $this->_helper->getValidity();
            endif;
            if ($validity) {
                if ($validity!==''):
                    if ($this->_request->getPostValue('del-date')==''):

                        $validDate= date('Y-m-d', strtotime($validity.' days'));
                    else:
                        $validDate= date('Y-m-d', strtotime(
                            $this->_request->getPostValue('del-date'). ' + '.$validity.' days'
                        ));
                    endif;
                endif;
            }
            $applied_categories= $item->getProduct()->getResource()->getAttributeRawValue(
                $item->getProduct()->getId(),
                'allowcategory',
                $this->_storeManager->getStore()->getId()
            );
            if ($giftcodeset!=''):
                $qtys = $item->getQty();
                for ($qty=1; $qty<=$qtys; $qty++) {
                    $quoteid=$item->getQuoteId();
                    $giftType = $item->getProduct()->getData('gifttype');
                    $data=['gift_card_value'=>$price,
                    'card_types'=> $giftType,
                    'template_id'=>$custom_upload,
                    'sender_name'=>$this->_request->getPostValue('sender-name'),
                    'sender_email'=>$this->_request->getPostValue('sender-email'),
                    'recipient_name'=>$this->_request->getPostValue('recipient-name'),
                    'recipient_email'=>$this->_request->getPostValue('recipient-email'),
                    'date_of_delivery'=>$this->_request->getPostValue('del-date'),
                    'message'=>$this->_request->getPostValue('giftmessage'),
                    'quote_id'=>$quoteid,
                    'product_id'=>trim($this->_request->getPostValue('giftproductid')),
                    'codesetid'=>$giftcodeset,
                    'expiry_date'=>$validDate,'customer_id'=>$logged_customer_id,
                    'categories'=>$applied_categories,
                    'temp_customer_id'=>$this->_request->getPostValue('temp_customer_id'),'timezone'=>$timezone,
                    'custom_upload'=>$custom_uploaded,
                    'emailtime'=>$new_date, 'sendtemplate_id'=>$sendtemplate_id,
                    'code_validity'=>$validity];
                    $this->_giftquote->setData($data);
                    $this->_giftquote->save();
                }
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
                  $metadata = $this->PublicCookieMetadata
                  ->setPath($this->_checkoutSession->getCookiePath())
                  ->setDomain($this->_checkoutSession->getCookieDomain());
                $this->cookieManager->setPublicCookie('temp_customer_id', $temp_customer_id, $metadata);
            endif;
        }
    }
}
