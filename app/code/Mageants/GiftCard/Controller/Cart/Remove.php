<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Cart;

use \Magento\Framework\App\Action\Context;
use \Mageants\GiftCard\Model\Giftquote;
use Magento\Checkout\Model\Cart as CustomerCart;
use \Magento\Checkout\Model\Session;
use \Magento\Quote\Model\Quote\Item;

/**
 * Remove quote from model
 */
class Remove extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Item
     */
    public $item;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var \Mageants\GiftCard\Model\Giftquote
     */
    protected $giftQuote;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    
    /**
     * @param Context $context
     * @param Giftquote $giftQuote
     * @param Session $session
     * @param Item $item
     * @param CustomerCart $cart
     */
    public function __construct(
        Context $context,
        Giftquote $giftQuote,
        Session $session,
        Item $item,
        CustomerCart $cart
    ) {
        $this->giftQuote=$giftQuote;
        $this->cart = $cart;
        $this->session = $session;
        $this->item = $item;
        parent::__construct($context);
    }

    /**
     * Perform Remove Action
     */
    public function execute()
    {
        $data=$this->getRequest()->getPostValue();
        try {
            if (!empty($data)) {
                $gifCodes = $this->giftQuote->load($data['quoteid']);
            
                $gifCodes->delete();

                $this->deleteQuoteItems($data['productId']);
            }
            $this->messageManager->addSuccess(__("Quote removed.."));
        } catch (Exception $ex) {
            $this->messageManager->addSuccess(__($ex->getMessage(), count($id)));
        }
    }

    /**
     * Remove cart item & quote
     *
     * @param object $productId
     * @return void
     */
    public function deleteQuoteItems($productId = '')
    {
        $checkoutSession = $this->getCheckoutSession();
        $allItems = $checkoutSession->getQuote()->getAllVisibleItems();//returns all teh items in session
        foreach ($allItems as $item) {
            if ((int)$item->getProduct()->getId()===(int)$productId) {
                $itemId = $item->getItemId();//item id of particular item
                $quoteItem=$this->getItemModel()->load($itemId);
                $this->cart->removeItem($itemId)->save();
            }
        }
    }

    /**
     * To check the Checkout session and return
     */
    public function getCheckoutSession()
    {
        $checkoutSession = $this->session;//checkout session
        return $checkoutSession;
    }
 
    /**
     * Return Item model
     */
    public function getItemModel()
    {
        $itemModel = $this->item;
        return $itemModel;
    }
}
