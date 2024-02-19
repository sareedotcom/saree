<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\ResponseFactory;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\Message\ManagerInterface;

/**
 * RemoveBlock Observer before render block
 */
class UpdateQuoteItemObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    
    /**
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ResponseFactory $responseFactory,
        UrlInterface $url,
        ManagerInterface $messageManager
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_messageManager = $messageManager;
    }

    /**
     * Update the Quote Item
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
      
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo()->toArray();
        
        foreach ($data as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if ($item->getProductType() == "giftcertificate" && $itemInfo['qty'] != $item->getQty()) {
                $CustomRedirectionUrl = $this->_url->getUrl('checkout/cart');
                $this->_messageManager->addNotice(__(
                    'You can not update Gift Card Qty Please update only main product Qty1.'
                ));
                $this->_responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
                exit(); // @codingStandardsIgnoreLine
            }
        }
    }
}
