<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\RequestInterface;
use \Mageants\GiftCard\Model\Giftquote;
use \Magento\Catalog\Model\Product;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Helper\Data;

/**
 * Configure product when update from cart
 */
class Updategift implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Mageants\GiftCard\Model\Giftquote
     */
    protected $_giftquote;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $modelProduct;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param RequestInterface $request
     * @param Giftquote $giftquote
     * @param Product $modelProduct
     * @param Session $checkoutSession
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        Giftquote $giftquote,
        Product $modelProduct,
        Session $checkoutSession,
        Data $helper
    ) {
        $this->_request = $request;
        $this->_helper=$helper;
        $this->modelProduct=$modelProduct;
        $this->_giftquote=$giftquote;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Configure product and update cart
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $post = $this->_request->getPostValue();
        $collection = $this->_giftquote->getCollection()->addFieldToFilter("product_id", $post['giftproductid'])
                                 ->addFieldToFilter("customer_id", $post['customerid']);
        if ($collection->getData()) {
            $id = $collection->getData()[0]['id'];
            if ($this->_request->getPostValue('manual-giftprices')!=''):
                $price = $this->_request->getPostValue('manual-giftprices');
            else:
                $price = $this->_request->getPostValue('giftprices');
            endif;
    
            $model = $this->_giftquote->load($id);
            $model->setGiftCardValue($price);
            $model->setTemplateId($post['giftimage']);
            $model->setSenderName($post['sender-name']);
            $model->setSenderEmail($post['sender-email']);
            $model->setRecipientName($post['recipient-name']);
            $model->setRecipientEmail($post['recipient-email']);
            if (array_key_exists('giftmessage', $post)) {
                $model->setMessage($post['giftmessage']);
            }
            if (array_key_exists('del-date', $post)) {
                $model->setDateOfDelivery($post['del-date']);
            }
            $model->save();
        
            $item = $observer->getItem();
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->save();

            $this->_checkoutSession->unsGiftCustomPrice();
            $this->_checkoutSession->unsGiftImage();
            $this->_checkoutSession->unsGiftSenderName();
            $this->_checkoutSession->unsGiftSenderEmail();
            $this->_checkoutSession->unsGiftRecipientName();
            $this->_checkoutSession->unsGiftRecipientEmail();
        }
    }
}
