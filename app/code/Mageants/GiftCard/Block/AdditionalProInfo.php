<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Model\Giftquote;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * AdditionalProInfo class for add aditional info in product view page
 */
class AdditionalProInfo extends \Magento\Framework\View\Element\Template
{
    
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
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;
        
    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Giftquote $quotes
     * @param Data $helper
     * @param CookieManagerInterface $cookieManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Giftquote $quotes,
        Data $helper,
        CookieManagerInterface $cookieManager,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_giftquote=$quotes;
        $this->_helper=$helper;
        $this->cookieManager = $cookieManager;
        parent::__construct($context, $data);
    }

    /**
     * Prepare constructor
     */
    // protected function _construct()
    // {
    //     parent::_construct();
    // }
    
    /**
     * Prepare Layout
     */
    // protected function _prepareLayout()
    // {
    //     parent::_prepareLayout();
    // }
 
    /**
     * Return to Additional data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return "Gift Card Details";
    }
    
    /**
     * Return to gift quote items by customer id
     *
     * @param string $customerid
     * @return object
     */
    
    public function getGiftQuoteItems($customerid = '')
    {
        if ($customerid != ''):
            return $this->_giftquote->getCollection()->addFieldToFilter('customer_id', $customerid);
        endif;
    }
    /**
     * Return to card type by type id
     *
     * @param string $typeid
     * @return array
     */
    public function getCardType($typeid = '')
    {
        $cardtype=['0'=>'Virtual','1'=>'Printed', '2'=>'Combined'];
        return $cardtype[$typeid];
    }
    /**
     * Save gift  quote
     *
     * @param int $quoteid
     * @return void
     */
    public function saveQuote($quoteid = '')
    {
        if ($quoteid!=''):
            $this->_checkoutSession->setGiftquote($quoteid);
        endif;
    }
    
    /**
     * Get Customer Id by helper
     *
     * @return integer
     */
    public function getcustomerId()
    {
        return $this->_helper->getCustomerId();
    }

    /**
     * Get not Logged customer Id by cokkiemanager
     *
     * @return integer
     */
    public function geNotLoggedIntcustomerId()
    {
        return $this->cookieManager->getCookie('temp_customer_id');
    }

    /**
     * Return Not loggedIn gift quote items
     *
     * @param string $temptd
     * @return object
     */
    public function getNotLoggedInGiftQuoteItems($temptd = '')
    {
        if ($temptd!=''):
            return $this->_giftquote->getCollection()->addFieldToFilter('temp_customer_id', $temptd);
        endif;
    }
}
