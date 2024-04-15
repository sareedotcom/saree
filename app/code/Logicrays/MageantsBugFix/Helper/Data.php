<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016  Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Logicrays\MageantsBugFix\Helper;

use \Magento\Store\Model\Website;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\GiftCard\Model\Templates;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Checkout\Model\Cart ;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Translate\Inline\StateInterface;
use \Magento\Framework\UrlInterface;
use \Mageants\GiftCard\Model\Codeset;
use \Mageants\GiftCard\Model\Codelist;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Catalog\Model\CategoryFactory;
use \Mageants\GiftCard\Model\Customer;
use \Magento\Framework\Pricing\Helper\Data as PricingData;
use \Mageants\GiftCard\Model\Account;
use \Magento\Customer\Model\SessionFactory;

/**
 * Data class for Helper
 */
class Data extends \Mageants\GiftCard\Helper\Data
{
    /**
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Mageants\GiftCard\Model\Templates
     */
    protected $_templates;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productrepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_modelSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Mageants\GiftCard\Model\Codeset
     */
    protected $_modelCodeset;

    /**
     * @var \Mageants\GiftCard\Model\Codelist
     */
    protected $_modelCodelist;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

     /**
      * @var \Magento\Framework\App\Config\ScopeConfigInterface
      */
    protected $_notificationConfig;
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $_modelAccount;
    
    /**
     * @var \Mageants\GiftCard\Model\Customer
     */
    protected $_modelCustomer;

    /**
     * @var PricingData
     */
    protected $currencyHelper;

    /**
     * @var SessionFactory
     */
    protected $sessionfactory;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $_indexerFactory;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    protected $_indexerCollectionFactory;

    /**
     * @param Website $website
     * @param StoreManagerInterface $storeManager
     * @param Templates $templates
     * @param StoreManagerInterface $url
     * @param LoggerInterface $logger
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $checkoutCart
     * @param ScopeConfigInterface $emailConfig
     * @param Session $modelSession
     * @param StateInterface $inlineTranslation
     * @param UrlInterface $urlInterface
     * @param Codeset $modelCodeset
     * @param Codelist $modelCodelist
     * @param TransportBuilder $transportBuilder
     * @param CategoryFactory $categoryFactory
     * @param Customer $modelCustomer
     * @param PricingData $currencyHelper
     * @param Account $modelAccount
     * @param SessionFactory $sessionfactory
     * @param \Magento\Indexer\Model\IndexerFactory $indexerFactory
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
     */
    public function __construct(
        Website $website,
        StoreManagerInterface $storeManager,
        Templates $templates,
        StoreManagerInterface $url,
        LoggerInterface $logger,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        Cart $checkoutCart,
        ScopeConfigInterface $emailConfig,
        Session $modelSession,
        StateInterface $inlineTranslation,
        UrlInterface $urlInterface,
        Codeset $modelCodeset,
        Codelist $modelCodelist,
        TransportBuilder $transportBuilder,
        CategoryFactory $categoryFactory,
        Customer $modelCustomer,
        PricingData $currencyHelper,
        Account $modelAccount,
        SessionFactory $sessionfactory,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexerCollectionFactory
    ) {
        $this->_categoryFactory=$categoryFactory;
        $this->_modelCodelist=$modelCodelist;
        $this->_modelCodeset=$modelCodeset;
        $this->_urlInterface=$urlInterface;
        $this->_modelSession=$modelSession;
        $this->_website = $website;
        $this->_storeManager = $storeManager;
        $this->_templates=$templates;
        $this->url = $url;
        $this->currencyHelper = $currencyHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_productrepository=   $productRepository;
        $this->checkoutCart=$checkoutCart;
        $this->_modelAccount=$modelAccount;
        $this->_modelCustomer=$modelCustomer;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_notificationConfig=$emailConfig;
        $this->_logger = $logger;
        $this->sessionfactory = $sessionfactory;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexerCollectionFactory = $indexerCollectionFactory;
    }

    /**
     * To get the Website Id and their collection
     *
     * @return Array
     */
    public function getWebsites()
    {
        $websites=$this->_website->getCollection();
        $options=[];
        foreach ($websites as $website) {
            $options[$website->getWebsiteId()]=['label'=>$website->getName(),'value'=>$website->getWebsiteId()];
        }
        return $options;
    }

    /**
     * Return Price Drop Down
     *
     * @param String $prices
     * @return String
     */
    public function getPriceDropdown($prices = '')
    {
        $html="";
        if ($prices!=''):
            $html.="<select name='giftprices' id='gift-prices' class='required gift-prices'>";
            if (!is_array($prices)):

                $html.="<option value=".$this->currencyHelper->currency(
                    $prices,
                    false,
                    false
                ).">".
                $this->currencyHelper->currency($prices, true, false)."</option>";
            else:
                foreach ($prices as $key => $price):
                    $html.="<option value=".$this->currencyHelper->currency(
                        $price['price'],
                        false,
                        false
                    ).">".
                    $this->currencyHelper->currency($price['price'], false, false)."</option>";
                endforeach;
            endif;
            $html.="</select>";
            return $html;
        endif;
    }

    /**
     * Return Currency Using storeManager
     *
     * @return String
     */
    public function getCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Return Currency Symbol Using storeManager
     *
     * @return Symbol
     */
    public function getCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * Return Gift Type using Dropdown
     *
     * @return string
     */
    public function getGiftTypeDropdown()
    {
        $html="<select name='gift-type' id='gift-types' class='required gift-type'>
                    <option value='0'>".__('Virtual')."</option>
                    <option value='1'>".__('Printed')."</option>
                    <option value='2'>".__('Both Virtual & Printed')."</option>
                </select>";
        return $html;
    }

    /**
     * Return Template
     *
     * @param String $templateid
     * @return String
     */
    public function getTemplate($templateid = '')
    {
        if ($templateid!=''):
            $html='';
            foreach ($templateid as $tplid):
                $templateCollection=$this->_templates->load($tplid);
                if ($templateCollection->getImageId()) {
                    $html.="<div class='giftemplates'><img src=".
                    $this->url->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ).$templateCollection->getImage()." class='template-image'
                    width='100px' height='50px' style='padding:5px;'/>
                        <input type='hidden' name='temp_id' value='"
                        .$templateCollection->getImageId()."' class='temp_id' />
                    </div>";
                }
            endforeach;
            return $html;
        endif;
    }

    /**
     * Return Validity for gift card
     *
     * @return String
     */
    public function getValidity()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/gcoption/gcvalidity',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check Status for gift card
     *
     * @return String
     */
    public function checkStatus()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/general/statusgc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Validity for gift card
     *
     * @return String
     */
    public function getStatus()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/general/statusgc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * To Allow DeliveryDate
     *
     * @return String
     */
    public function isAllowDeliveryDate()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/gcoption/allowdelvdate',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Email Template
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/gifttemplate',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Card Self use
     *
     * @return string
     */
    public function allowSelfUse()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/gcoption/allowselfuse',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Using the BCC email
     *
     * @return String
     */
    public function getBcc()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/cc',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * To redirect after login
     *
     * @return Login Object
     */
    public function loginRedirect()
    {
        $login=$this->isLoggedIn();
        if ($login==0):
            $this->_messageManager->addError("Please login to get giftcards");
            $customer = $this->sessionfactory->create();
            $customerSession = $customer;
            $urlInterface = $this->_urlInterface;
            $customerSession->setAfterAuthUrl($urlInterface->getCurrentUrl());
            $customerSession->authenticate();
        endif;
        return $login;
    }

    /**
     * CheckCustomer Login
     *
     * @return Login Object
     */
    public function checkCustomerLogin()
    {
        $login=$this->isLoggedIn();
        return $login;
    }

    /**
     * Return CustomerId using SessionFactory
     *
     * @return int
     */
    public function getCustomerId()
    {
        $customer = $this->sessionfactory->create();
        return  $customer->getCustomer()->getId();
    }

    /**
     * Use is LoggedIn
     *
     * @return int
     */
    public function isLoggedIn()
    {
        $customer = $this->sessionfactory->create();
        if (!$customer->isLoggedIn()) {
            return 0;
        }
        return 1;
    }

    /**
     * Product availibility
     *
     * @param codeset $codeset
     * @return int
     */
    public function availibilityProduct($codeset = '')
    {
        if ($codeset!=''):
            
            $codesettitle = $this->_modelCodeset->getCollection()->addFieldToFilter('code_title', $codeset);
            $id=0;
            foreach ($codesettitle as $code) {
                $id=$code->getCodeSetId();
            }
            $codelist = $this->_modelCodelist->getCollection()->addFieldToFilter(
                'code_set_id',
                $id
            )->addFieldToFilter('allocate', 0);
            if (empty($codelist->getData())):
                return 0;
            endif;
            return 1;
        endif;
    }

    /**
     * Return Product Stock
     *
     * @param string $id
     * @return int
     */
    public function setProductStock($id = '')
    {
        if ($id!=''):
            $product = $this->_productrepository->getById($id);
            $product->setQuantityAndStockStatus(['qty' => 0, 'is_in_stock' => 0]);
            $this->_productrepository->save($product);
        endif;
    }

    /**
     * Return Quote By Id for product
     *
     * @param string $prdId
     * @return int
     */
    public function getCartQuoteById($prdId = '')
    {
        if ($prdId != '') {
            $cartData = $this->checkoutCart->getQuote()->getAllVisibleItems();
            $cartDataCount = count($cartData);
            if ($cartDataCount > 0) {
                foreach ($cartData as $item) {
                    $productId = $item->getProduct()->getId();
                    if ($prdId==$productId) {
                        return 1;
                    }
                }
                return 0;
            } else {
                return 0;
            }
        }
        return 0;
    }

    /**
     * Return Categories Name
     *
     * @param string $catId
     * @return config
     */
    public function getCategoriesName($catId = '')
    {
        if (!empty($catId)) {
             $_category = $this->_categoryFactory->create()->load($catId);
             return $_category->getName();
        }
        return null;
    }

    /**
     * Return Config Value
     *
     * @param string $path
     * @param string $storeId
     * @return coNFIG
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
 
    /**
     * Return Store
     *
     * @return $this
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
 
    /**
     * Return Template Id
     *
     * @param string $xmlPath
     * @return int
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }
 
    /**
     * Create Template
     *
     * @param string $emailTemplateVariables
     * @param string $senderInfo
     * @param string $receiverInfo
     * @return $this
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $emailTemplate = 'giftcertificate_email_gifttemplate';
        if ($this->getEmailTemplate()!='') {
            $emailTemplate = $this->getEmailTemplate();
        }
    
        $template =  $this->_transportBuilder->setTemplateIdentifier(trim($emailTemplate))
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
 
    /**
     * Send Template in Email
     *
     * @param string $emailTemplateVariables
     */
    public function sendTemplate($emailTemplateVariables)
    {
        $emailTemplate = 'giftcertificate_email_gifttemplate';
        if ($this->getEmailTemplate()!='') {
            $emailTemplate = $this->getEmailTemplate();
        }
        
        $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($emailTemplateVariables);
        $sender = [
        'name' => $emailTemplateVariables['sender_name'],
        'email' => $emailTemplateVariables['sender_email'],
        ];
        
        $bcc='test@giftcertificate.com';
        if (isset($emailTemplateVariables['bcc'])) {
            $bcc=$emailTemplateVariables['bcc'];
        }
       
           $transport = $this->_transportBuilder
            ->setTemplateIdentifier(trim($emailTemplate))->setTemplateOptions([
             'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
             'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
             ]) // My email template
             ->setTemplateVars(['data' =>$postObject])
             ->setFrom($sender)
             ->addBcc($bcc)
             ->addTo($emailTemplateVariables['recipient_email'])
             ->getTransport();
        try {
            //var_dump('yes');exit;
            //var_dump($transport->sendMessage());exit;
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    /**
     * Send Notification
     *
     * @param string $emailTemplateVariables
     */
    public function sendNotification($emailTemplateVariables = '')
    {
        $emailTemplate = 'giftcertificate_email_notify_template';
        if ($this->getEmailTemplate()!='') {
            $emailTemplate = $this->getEmailTemplate();
        }
        $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($sender);
        if ($this->_notificationConfig->getValue(
            'giftcertificate/gcoption/advancenotification',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )):
        
               $customerAccount = $this->_modelAccount->getCollection();
            foreach ($customerAccount as $code) {
                if ($code->getExpireAt()!='0000-00-00'):
                    $currentDate= date('Y-m-d');
                    if ($currentDate < $code->getExpireAt()):
                        $customerid=$code->getOrderId();
                        $customer_data = $this->_modelCustomer->load($customerid);
                                $emailTemplateVariables['message'] = "Your Gift Card is going to expire";
                                $emailTemplateVariables['current_balance'] =$code->getCurrentBalance();
                                $emailTemplateVariables['code'] =$code->getGiftCode();
                                $emailTemplateVariables['recipient_name'] = $customer_data->getRecipientName();
                                $emailTemplateVariables['recipient_email'] = $customer_data->getRecipientEmail();
                                $emailTemplateVariables['category_name']=$code->getCategories();
                                $sender = [
                                    'name' => $customer_data->getSenderName(),
                                    'email' => $customer_data->getSenderEmail(),
                                    ];
                                try {
                                        $postObject = new \Magento\Framework\DataObject();
                                        $postObject->setData($emailTemplateVariables);
                                        $transport = $this->_transportBuilder->setTemplateIdentifier(trim($emailTemplate))// @codingStandardsIgnoreLine
                                        ->setTemplateOptions([
                                         'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                         'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                         ])
                                        ->setTemplateVars(['data' =>$postObject])
                                        ->setFrom($sender)
                                        ->addTo($customer_data->getRecipientEmail())
                                        ->getTransport();
                                    $transport->sendMessage();
                                     $this->inlineTranslation->resume();
                                } catch (Exception $ex) {
                                    $this->_logger->addDebug($ex->getMessage());
                                }

                    endif;
                endif;
            }
        endif;
    }

    /**
     * To allowtimezone
     */
    public function isallowtimezone()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/is_timezone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * To allowimageupload
     */
    public function isallowimageupload()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/gcoption/allow_custom_upload',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * To notifyBefore
     */
    public function notifyBefore()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/notify_before',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * To Notify
     */
    public function isNotify()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/is_notify',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return NotifyTemplate
     */
    public function getNotifyTemplate()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/notify_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return SendTime
     */
    public function getSendTime()
    {
        return $this->_scopeConfig->getValue(
            'giftcertificate/email/start_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return reIndexing
     */
    public function reIndexing()
    {
        $indexerCollection = $this->_indexerCollectionFactory->create();
        $ids = $indexerCollection->getAllIds();
        foreach ($ids as $id) {
            $idx = $this->_indexerFactory->create()->load($id);
            $idx->reindexAll($id);
        }
    }
}
