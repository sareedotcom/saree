<?php
 
namespace Mageants\GiftCard\Plugin;
 
class Product
{   
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_httpRequest;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Request\Http $httpRequest
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $httpRequest
    ){
        $this->_checkoutSession = $checkoutSession;
        $this->_httpRequest = $httpRequest;
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {   
        $currentAction = $this->_httpRequest->getFullActionName();

        if($currentAction =='catalog_product_view')
        {
              $this->_checkoutSession->setCurrentUrl('productpage');
        }

        $checkUrl = $this->_checkoutSession->getCurrentUrl();
        $price = 0;
        $price = $this->_checkoutSession->getGiftcardPrice();

        if($subject->getTypeId() == 'giftcertificate' && $checkUrl == 'checkoutPage')
        {
            return $price;
        }
        return $result;
    }
}