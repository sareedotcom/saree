<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Cart;

use Magento\Framework;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Data\Form\FormKey\Validator;
use \Magento\Quote\Model\Quote\Item;
use \Magento\Checkout\Model\Cart;
use \Magento\Customer\Model\Session as Customersession;
use \Mageants\GiftCard\Model\Giftquote;
use \Magento\Catalog\Helper\Product\View;
use \Psr\Log\LoggerInterface;

class Configure extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var LoggerInterface
     */
    public $log;

    /**
     * @var View
     */
    public $view;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_cartQuoteItem;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerData;

    /**
     * @var \Mageants\GiftCard\Model\Giftquote
     */
    protected $_giftQuote;
    
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param Item $cartQuoteItem
     * @param Cart $cart
     * @param Customersession $customerData
     * @param View $view
     * @param LoggerInterface $log
     * @param Giftquote $giftQuote
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Item $cartQuoteItem,
        Cart $cart,
        Customersession $customerData,
        View $view,
        LoggerInterface $log,
        Giftquote $giftQuote
    ) {
        $this->_cartQuoteItem=$cartQuoteItem;
        $this->_customerData=$customerData;
        $this->_giftQuote=$giftQuote;
        $this->view=$view;
        $this->log=$log;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
    }

    /**
     * Action to reconfigure cart item
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Extract item and product to configure
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }
        
        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addError(__("We can't find the quote item."));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }
            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->view->prepareAndRender(
                $resultPage,
                $quoteItem->getProduct()->getId(),
                $this,
                $params
            );
            $customerId= $this->_customerData->getCustomer()->getId();
            $collection=$this->_cartQuoteItem->getCollection()->addFieldToFilter(
                'product_id',
                $productId
            )->addFieldToFilter('item_id', $id);

            foreach ($collection->getData() as $giftcardQuote) {
                 $this->_checkoutSession->setGiftCustomPrice($giftcardQuote['custom_price']);
            }
            $quoteCollection=$this->_giftQuote->getCollection()->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter('product_id', $productId);
            foreach ($quoteCollection as $quote) {
                $this->_checkoutSession->setGiftImage($quote->getTemplateId());
                $this->_checkoutSession->setGiftSenderName($quote->getSenderName());
                $this->_checkoutSession->setGiftSenderEmail($quote->getSenderEmail());
                $this->_checkoutSession->setGiftRecipientName($quote->getRecipientName());
                $this->_checkoutSession->setGiftRecipientEmail($quote->getRecipientEmail());
                $this->_checkoutSession->setMessage($quote->getMessage());
              
                $this->_checkoutSession->setDateOfDelivery($quote->getDateOfDelivery());

            }
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->log->critical($e);
            return $this->_goBack();
        }
    }
}
