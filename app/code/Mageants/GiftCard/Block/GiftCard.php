<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Config\Model\Config\Source\Locale\Timezone;
use \Magento\Framework\App\Request\Http;

/**
 * GiftCard Class for giftCertificate
 */
class GiftCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    
       /**
        * @var \Magento\Store\Model\StoreManagerInterface
        */
    protected $_storeManager;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */

    protected $_timezone;
    
    /**
     * @param Context $context
     * @param Data $helper
     * @param Session $checkoutSession
     * @param Registry $registry
     * @param ManagerInterface $messageManager
     * @param Timezone $timezone
     * @param Http $request
     */
    public function __construct(
        Context $context,
        Data $helper,
        Session $checkoutSession,
        Registry $registry,
        ManagerInterface $messageManager,
        Timezone $timezone,
        Http $request
    ) {
        $this->_registry = $registry;
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $context;
        $this->_messageManager = $messageManager;
        $this->_request= $request;
        $this->_timezone=$timezone;
        
        if ($this->_registry->registry('product')->getTypeId()=='giftcertificate'):
            if (!$this->_helper->availibilityProduct($this->_registry->registry(
                'product'
            )->getAttributeText('giftcerticodeset'))):
                $this->_helper->setProductStock($this->_registry->registry('product')->getId());
                $this->_messageManager->addError("Out Of Stock");
            endif;
        endif;
        parent::__construct($context);
    }
     
    /**
     * Return Product Type Id
     *
     * @return int
     */
    public function getProductTypeId()
    {
        return $this->getProduct()->getTypeId();
    }
     
    /**
     * Return Gift Custom Price
     *
     * @return string
     */
    public function getGiftCustomPrice()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftCustomPrice();
        }
        return null;
    }

    /**
     * Return Gift image
     *
     * @return string
     */
    public function getGiftImage()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftImage();
        }
           return null;
    }
     
    /**
     * Return Gift sender name
     *
     * @return string
     */
    public function getGiftSenderName()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftSenderName();
        }
         return null;
    }

    /**
     * Return Gift sender email
     *
     * @return string
     */
    public function getGiftSenderEmail()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftSenderEmail();
        }
           return null;
    }

    /**
     * Return Gift Recipient name
     *
     * @return string
     */
    public function getGiftRecipientName()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftRecipientName();
        }
        return null;
    }

    /**
     * Return Gift Recipient email
     *
     * @return string
     */
    public function getGiftRecipientEmail()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getGiftRecipientEmail();
        }
         return null;
    }
    /**
     * Return delivery date
     *
     * @return date
     */
    public function getDateOfDelivery()
    {
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getDateOfDelivery();
        }
         return null;
    }

    /**
     * Return message
     *
     * @return string
     */
    public function getMessage()
    {
           
        if ($this->_request->getParam('product_id')) {
            return $this->_checkoutSession->getMessage();
        }
        return null;
    }
    
    /**
     * Return Product using registry
     *
     * @return object
     */
    public function getProduct()
    {
        return $this->_registry->registry('product');
    }
     
    /**
     * Return Product price
     *
     * @return array
     */
    public function getProductPrice()
    {
        if (!empty($this->getProduct()->getTierPrice())):
            $prices=$this->_helper->getPriceDropdown($this->getProduct()->getTierPrice());
            return $prices;
        else:
             $prices=$this->_helper->getPriceDropdown($this->getProduct()->getPrice());
             return $prices;
        endif;
    }
     
    /**
     * Return currency
     *
     * @return int
     */
    public function getCurrency()
    {
        return $this->_helper->getCurrency();
    }
     
    /**
     * Return product and gift type
     *
     * @return object
     */
    public function getGiftType()
    {
         return $this->getProduct()->getAttributeText('gifttype');
    }
     
    /**
     * Return gift type and their option
     *
     * @return object
     */
    public function getGiftTypeOption()
    {
        if ($this->getGiftType()=='Combined'):
            return $this->_helper->getGiftTypeDropdown();
        endif;
         return '';
    }
    
    /**
     * Return gift templates
     *
     * @return int
     */
    public function getGiftTemplates()
    {
        $template=$this->getProduct()->getResource()->getAttributeRawValue(
            $this->getProduct()->getId(),
            'giftimages',
            $this->_storeManager->getStore()->getId()
        );
        $templateid=explode(',', $template);
        return $templateid;
    }
    
    /**
     * Return template images
     *
     * @return string
     */
    public function getTemplateImages()
    {
        return $this->_helper->getTemplate($this->getGiftTemplates());
    }
    
    /**
     * To set permission allow Greetings
     *
     * @return int
     */
    public function isallowGreetings()
    {
        if ($this->getProduct()->getAttributeText('allowmessage')=='No'):
            return 0;
        endif;
        return 1;
    }
     
    /**
     * @inheritdoc
     */
    public function isAllowDeliveryDate()
    {
        return $this->_helper->isAllowDeliveryDate();
    }
    
    /**
     * @inheritdoc
     */
    public function isLoggedIn()
    {
        return $this->_helper->checkCustomerLogin();
    }
    
    /**
     * Return code set id
     *
     * @return string
     */
    public function getCodeSetId()
    {
        return $this->getProduct()->getAttributeText('giftcerticodeset');
    }
    
    /**
     * Return Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_helper->getCustomerId();
    }
    
    /**
     * Return min price
     *
     * @return int
     */
    public function getMinPrice()
    {
        return $this->getProduct()->getMinprice();
    }

    /**
     * Return max price
     *
     * @return int
     */
    public function getMaxPrice()
    {
        return $this->getProduct()->getMaxprice();
    }
    
    /**
     * To check the availibility product
     *
     * @return object
     */
    public function availibilityProduct()
    {
        return $this->_helper->availibilityProduct($this->getProduct()->getAttributeText('giftcerticodeset'));
    }

    /**
     * Return Cart Quote By Id
     *
     * @param string $prdId
     * @return object
     */
    public function getCartQuoteById($prdId = '')
    {
        return $this->_helper->getCartQuoteById($prdId);
    }

    /**
     * Return temp Customer Id
     *
     * @param string $prdId
     * @return object
     */
    public function getTempCustomerId($prdId = '')
    {
        return random_int(000000, 999999);
    }

    /**
     * Return Time zone list
     *
     * @return array
     */
    public function getTimeZoneList()
    {
        return $this->_timezone->toOptionArray();
    }

    /**
     * To set is allow time zone
     *
     * @return boolean
     */
    public function isallowtimezone()
    {
        return $this->_helper->isallowtimezone();
    }

    /**
     * To set is allow image upload
     *
     * @return boolean
     */
    public function isallowimageupload()
    {
         return $this->_helper->isallowimageupload();
    }
}
