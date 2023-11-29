<?php

namespace Mageants\GiftCard\Plugin;

use \Magento\Checkout\Model\Session;
use \Magento\Framework\Stdlib\CookieManagerInterface;
use \Mageants\GiftCard\Model\Giftquote;

class AccountManagement
{
    /**
     * @var Session
     */
    public $session;

    /**
     * @var CookieManagerInterface
     */
    public $cookieManager;

    /**
     * @var Giftquote
     */
    public $_giftquote;
    
    /**
     * @param Session $session
     * @param CookieManagerInterface $cookieManager
     * @param Giftquote $giftquote
     */
    public function __construct(
        Session $session,
        CookieManagerInterface $cookieManager,
        Giftquote $giftquote
    ) {
        $this->session = $session;
        $this->cookieManager = $cookieManager;
        $this->_giftquote=$giftquote;
    }
    /**
     * Authenticate a customer
     *
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterface $result
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function afterAuthenticate(
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        $result
    ) {
        $customerid = $result->getId();
        $items = $this->session->getQuote()->getAllVisibleItems();
        if ($items) {
            foreach ($items as $item) {
                   $product_id =  $item->getProduct()->getId();
                if ($item->getProductType()=='giftcertificate') {
                    if ($this->cookieManager->getCookie('temp_customer_id')!==null) {
                        $this->checkCustomerdetail($product_id, $customerid);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Check customer and product detail
     *
     * @param string $product_id
     * @param string $customerid
     */
    public function checkCustomerdetail($product_id, $customerid)
    {
        $oldQuotes = $this->_giftquote->getCollection()->
        addFieldToFilter('product_id', $product_id)->
        addFieldToFilter('customer_id', $customerid);
            
        if ($oldQuotes) {
            foreach ($oldQuotes as $old) {
                $gift = $this->_giftquote->load($old->getId());
                $gift->delete();

            }
        }
        $temp_customer_id = $this->cookieManager->getCookie('temp_customer_id');
         $quoteCollection = $this->_giftquote->getCollection()->
         addFieldToFilter('product_id', $product_id)->
         addFieldToFilter('temp_customer_id', $temp_customer_id);

        foreach ($quoteCollection as $giftquote) {
            if ($giftquote->getCustomerId() == 0) {
                $data = ['customer_id'=>$customerid, 'id'=>$giftquote->getId()];
                $this->_giftquote->setData($data);
                $this->_giftquote->save();
            }
        }
    }
}
