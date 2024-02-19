<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Configure product when update from cart
 */
class CheckShoppingCartObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Configure product and update cart
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $this->_checkoutSession->getQuote()->getItems();
        if ($this->_checkoutSession->getGiftquote()=='') {
            if ($items) {
                foreach ($items as $item) {
                    if ($item->getProductType()=="giftcertificate") {
                        $this->_checkoutSession->setGiftquote($item->getQuoteId());
                    }

                }
            }
        }
    }
}
