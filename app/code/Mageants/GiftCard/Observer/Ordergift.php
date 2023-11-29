<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use \Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use \Mageants\GiftCard\Model\Giftquote;
use \Psr\Log\LoggerInterface;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Model\Customer;
use \Mageants\GiftCard\Model\Account;
use \Mageants\GiftCard\Model\Codeset;
use \Mageants\GiftCard\Model\Codelist;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Order Gift observer for place order event
 */
class Ordergift implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    public $_request;

    /**
     * @var \Mageants\GiftCard\Model\Giftquote
     */
    protected $_giftquote;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var \Mageants\GiftCard\Model\Customer
     */
    protected $_customer;
    
    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $_account;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Mageants\GiftCard\Model\Codeset
     */
    protected $_codeset;
    
    /**
     * @var \Mageants\GiftCard\Model\Codelist
     */
    protected $_codelist;
    
    /**
     * @var \Mageants\GiftCard\Helper\Email
     */
    protected $_email;
    
    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

      /**
       * @var \Magento\Framework\Stdlib\CookieManagerInterface
       */
    protected $cookieManager;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param Giftquote $giftquote
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Customer $customer
     * @param Account $account
     * @param Codeset $codeset
     * @param Codelist $codelist
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        RequestInterface $request,
        Giftquote $giftquote,
        LoggerInterface $logger,
        Session $checkoutSession,
        Customer $customer,
        Account $account,
        Codeset $codeset,
        Codelist $codelist,
        StoreManagerInterface $storeManager,
        Data $helper,
        CookieManagerInterface $cookieManager
    ) {
        $this->_giftquote=$giftquote;
        $this->_logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_request = $request;
        $this->_customer = $customer;
        $this->_account = $account;
        $this->_storeManager = $storeManager;
        $this->_codeset=$codeset;
        $this->_codelist=$codelist;
        $this->_helper=$helper;
        $this->cookieManager = $cookieManager;
    }

    /**
     * To check Order detail
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        if ($this->_checkoutSession->getAccountid()!='' && $this->_checkoutSession->getGift()!=''):
            $updateOrder = $observer->getEvent()->getOrder();
            $updateOrder->setCouponRuleName('gift_certificate')->save();
            $updateOrder->setOrderGift($this->_checkoutSession->getGift())->save();
            $this->updateBalance();
            return;
        endif;
        
        if ($this->_checkoutSession->getGiftquote()):
            $order = $observer->getEvent()->getOrder();
            $order_id = $order->getIncrementId();
            $items =$order->getAllVisibleItems();
            $productIds = [];
            if ($this->_helper->getCustomerId()!==null) {
                $gift_quotes= $this->_giftquote->getCollection()->addFieldToFilter(
                    'customer_id',
                    $this->_helper->getCustomerId()
                );
            } else {
                $gift_quotes= $this->_giftquote->getCollection()->addFieldToFilter(
                    'temp_customer_id',
                    $this->cookieManager->getCookie('temp_customer_id')
                );
            }

            $quote_id = $this->getItemdetail($items, $gift_quotes, $order_id);

            $this->_checkoutSession->unsGiftquote();
            $this->_checkoutSession->setGiftCardCode("");
        endif;
    }

    /**
     * To save item detail in quote id
     *
     * @param object $items
     * @param object $gift_quotes
     * @param object $order_id
     */
    public function getItemdetail($items, $gift_quotes, $order_id)
    {
        $quote_id=[];
        foreach ($items as $item) {
            if ($item->getProductType()=='giftcertificate'):
                foreach ($gift_quotes as $gift) {
                    $id = '';
                    if ($gift->getProductId()==$item->getProductId()):
                        $quote_id[]=$gift->getId();
                        $codesetModel=$this->_codeset->getCollection()->addFieldToFilter(
                            'code_title',
                            trim($gift->getCodesetid())
                        );
                        foreach ($codesetModel as $codeset) {
                            $id=$codeset->getId();
                        }
                        if ((int)$id):
                            $certificateCode = [];
                            $order_id = $this->applyCode($id, $order_id, $gift);
                        endif;
                    endif;
                    if (!empty($quote_id)):
                        foreach ($quote_id as $id) {
                            $quote=$this->_giftquote->load($id);
                            $quote->setOrderIncrementId($order_id);
                            $quote->save();
                        }
                    endif;
                }
            endif;
            return $quote_id;
        }
    }

    /**
     * To set customer data
     *
     * @param object $id
     * @param object $order_id
     * @param object $gift
     */
    public function applyCode($id, $order_id, $gift)
    {
        $codes=$this->_codelist->getCollection()->addFieldToFilter('code_set_id', (int)$id);
        $applicableCodes='';
        foreach ($codes as $giftcode) {
            if ($giftcode->getAllocate()==0):
                
                $applicableCodes=$giftcode->getCode();
                $code_list_id=$giftcode->getCodeListId();
                if ($code_list_id):
                    try {
                        $updatecode=['code_list_id'=>$code_list_id,'allocate'=>1];
                        $this->_codelist->setData($updatecode);
                        $this->_codelist->save();
                    } catch (Exception $e) {
                        $this->messageManager->addError(__("We can't find the code list id."));
                    }
                endif;
                break;
            endif;
        }
        $certificateCode=[];
        if ($applicableCodes!=''):
            $certificateCode[]=$applicableCodes;
            $customerdata=
            [
                'code_value'=>$gift->getGiftCardValue(),
                'card_type'=>$gift->getCardTypes(),
                'sender_name'=>$gift->getSenderName(),
                'sender_email'=>$gift->getSenderEmail(),
                'recipient_name'=>$gift->getRecipientName(),
                'recipient_email'=>$gift->getRecipientEmail(),
                'date_of_delivery'=>$gift->getDateOfDelivery(),
                'message'=>$gift->getMessage(),
                'order_id'=>$order_id,
                'timezone'=>$gift->getTimezone(),
                'emailtime'=>$gift->getEmailtime()
            ];
            
            $this->_customer->setData($customerdata);
            $orderid=$this->_customer->save()->getId();
            $accountdata=
            [
                'order_id'=>$orderid,
                'status'=>'0',
                'website'=>$this->_storeManager->getStore()->getWebsiteId(),
                'initial_code_value'=>$gift->getGiftCardValue(),
                'current_balance'=>$gift->getGiftCardValue(),
                'comment'=>$gift->getMessage(),
                'gift_code'=>$applicableCodes,
                'expire_at'=>$gift->getExpiryDate(),
                'template'=>$gift->getTemplateId(),
                'customer_id'=>$gift->getCustomerId(),
                'categories'=>$gift->getCategories(),
                'custom_upload'=>$gift->getCustomUpload(),
                'sendtemplate_id'=>$gift->getSendtemplateId(),
                'order_increment_id'=>$order_id
            ];
            $this->_account->setData($accountdata);
            $this->_account->save();
        endif;
            return $order_id;
    }

    /**
     * Update balance and unset session
     */
    public function updateBalance()
    {
        $status=1;
        if ($this->_checkoutSession->getGiftbalance()===0 || $this->_checkoutSession->getGiftbalance()=='0'):
            $status=0;
        endif;
        $accountdata=['status'=>$status,'current_balance'=>$this->_checkoutSession->
        getGiftbalance(),'account_id'=>$this->_checkoutSession->getAccountid()];
        $this->_account->setData($accountdata);
        $this->_account->save();
        $this->_checkoutSession->unsGiftbalance();
        $this->_checkoutSession->unsAccountid();
        $this->_checkoutSession->unsGift();

        $this->_checkoutSession->unsGiftCardCode();
    }
}
