<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block;

use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Checkout\Model\Cart;
use \Magento\Checkout\Model\Session;

class Giftcode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @param Data $helper
     * @param Context $context
     * @param Cart $cart
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Context $context,
        Cart $cart,
        Session $checkoutSession,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_cart = $cart;
        $this->_checkoutSession=$checkoutSession;
        parent::__construct($context);
    }
    
    /**
     * @inheritdoc
     */
    public function isLoggedIn()
    {
        return $this->_helper->isLoggedIn();
    }
    
    /**
     * To check cart is empty or not
     *
     * @return int
     */
    public function isCartEmpty()
    {
        if (empty($this->_cart->getQuote()->getAllItems())) {
            return 1;
        }
        return 0;
    }

    /**
     * Return the Gift card code using session
     */
    public function getGiftCardCode()
    {
        return $this->_checkoutSession->getGiftCardCode();
    }
}
