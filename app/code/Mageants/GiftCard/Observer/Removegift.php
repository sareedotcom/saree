<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Checkout\Model\Session;

/**
 * Configure product when update from cart
 */
class Removegift implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param Session $checkoutSession
     */

    public function __construct(
        Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Remove gift card from discount charge
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       
        if ($this->_checkoutSession->getGift()) {
            $this->_checkoutSession->setGiftCardCode("");
            $this->_checkoutSession->unsGift();
        }
    }
}
