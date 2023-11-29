<?php


namespace Mageants\GiftCard\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Framework\Escaper;
use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Checkout\Model\Cart;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Account;

/**
 * Coupon management object.
 */
class GiftCardManagement implements \Mageants\GiftCard\Api\GiftCardManagementInterface
{
    
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    
    /**
     * @var accountFactory
     */
    private $accountFactory;
    
    /**
     * @var quoteFactory
     */
    private $quoteFactory;
    
    /**
     * @var giftQuoteRepository
     */
    private $giftQuoteRepository;
    
    /**
     * @var quoteResource
     */
    private $quoteResource;
    
    /**
     * @var Escaper
     */
    private $escaper;
    
    /**
     * @var codeRepository
     */
    private $codeRepository;
    
    /**
     * @var collectionFactory
     */
    private $collectionFactory;
    
    /**
     * @var codes
     */
    private $codes = [];

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Escaper $escaper
     * @param Context $context
     * @param ession $checkoutSession
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     * @param Cart $cart
     * @param Codelist $codelist
     * @param Account $account
     */

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Escaper $escaper,
        Context $context,
        session $checkoutSession,
        Data $helper,
        JsonFactory $resultJsonFactory,
        Cart $cart,
        Codelist $codelist,
        Account $account
    ) {
        $this->quoteRepository = $quoteRepository;
      
        $this->escaper = $escaper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession=$checkoutSession;
        $this->cart = $cart;
        $this->codelist = $codelist;
        $this->account = $account;
        $this->_helper=$helper;
    }

    /**
     * @inheritdoc
     */
    public function set($cartId, $giftCard)
    {
        
        $result_return = $this->resultJsonFactory->create();
        $this->_checkoutSession->unsGift();
        $catids=$this->getCategories();

        $subtotal=0;
        $gifCodes = $this->codelist;
        $availableCode = $this->account->getCollection()
        ->addFieldToFilter('gift_code', trim($giftCard))
        ->addFieldToFilter('status', 1);

        if (empty($availableCode->getData())):

            $error= "<span style='color:#f00'>Invalid Gift Card</span>";

            $result=[0=>'1',1=>$error];
            return $result_return->setData($result);

        else:
            $cat_array=[];

            foreach ($availableCode as $catlist) {

                $cat_array=explode(",", $catlist->getCategories());

            }
            
            $result_return = $this->getGiftcat($cat_array, $catids, $availableCode);

        endif;

       // return $giftCard;
    }

    /**
     * Retuen Gift category
     *
     * @param array $cat_array
     * @param object $catids
     * @param object $availableCode
     */
    public function getGiftcat($cat_array, $catids, $availableCode)
    {
        if (!empty($cat_array)):

            if (!is_array($catids)) {

                $key=array_search($catids, $cat_array);
        
                if (!$key) {

                    $error= "<span style='color:#f00'>Sorry,
                  Gift Card not available for this category/Categories</span>";
                    $result=[0=>'5',1=>$error];
                    
                    return $result_return->setData($result);
                }
            } else {

                $this->getKeydata($catids, $cat_array, $key);
            }

        endif;

         $certificate_value=0;

        foreach ($availableCode as $code) {

            if ($code->getCurrentBalance()==0):

                $error= "<span style='color:black'>You Don't have enough balance.</span>";

                $result=[0=>'2',1=>$error];
                return $result_return->setData($result);

            endif;

            if (!$this->_helper->allowSelfUse()):

                if ($code->getCustomerId()==$this->_helper->getCustomerId()):

                    $error= "<span style='color:#f00'>Sorry, You cannot use certificate for yourself</span>";

                    $result=[0=>'4',1=>$error];
                    return $result_return->setData($result);

                endif;

            endif;

            if ($code->getExpireAt()!='0000-00-00' && $code->getExpireAt()!='1970-01-01'):

                $currentDate= date('Y-m-d');

                if ($currentDate > $code->getExpireAt()):

                    $error= "<span style='color:#f00'>Sorry, This Gift Card Has Been Expired</span>";

                    $result=[0=>'4',1=>$error];
                    return $result_return->setData($result);

                endif;

            endif;

            $certificate_value=  $code->getCurrentBalance();

            $quote = $this->cart;
            $totals = $quote->getQuote()->getTotals();
            $cartSubtotal = $totals['subtotal']['value'];
            if ($subtotal != $cartSubtotal) {
                $subtotal = $cartSubtotal;
            }

               $gift_value=$subtotal;
                
            if ($certificate_value < $subtotal) {
                $gift_value=$certificate_value;
            }

            $accund_id=$code->getAccountId();
            $updateblance=$code->getCurrentBalance() - $gift_value;

            if ($code->getDiscountType() == "percent") {
                $certificate_value = $code->getPercentage();

                if ($certificate_value >= 100) {
                    $discount = 1;
                } else {
                    $discount = $certificate_value;
                }
                $gift_value = ($code['current_balance']*$discount)/100;
                                            
                if ($gift_value > (int)$cartSubtotal) {
                    $gift_value = (int)$cartSubtotal;
                } elseif ($gift_value > $code->getCurrentBalance()) {
                    $gift_value = $code->getCurrentBalance();
                }

                $updateblance = $code->getCurrentBalance() - $gift_value;
            }

            // $_SESSION['custom_gift'] = $gift_value;
            $result=[0=>'3',1=>'Gift Card Accepted'];
            $result_return->setData($result);

            $this->_checkoutSession->setGift($gift_value);
            $this->_checkoutSession->setGiftCardCode($code->getGiftCode());

            $this->_checkoutSession->setAccountid($accund_id);

            $this->_checkoutSession->setGiftbalance($updateblance);
            $this->_checkoutSession->getQuote()->collectTotals()->save();
            $cartQuote = $quote->getQuote();
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            return $result_return;

        }
    }

    /**
     * Retuen Key data
     *
     * @param object $catids
     * @param array $cat_array
     * @param object $key
     */
    public function getKeydata($catids, $cat_array, $key)
    {
        $key_val=0;
        $check_flag=false;
        foreach ($catids as $catid) {

            $id=explode(",", $catid);

            $size=count($id);

            foreach ($id as $i) {

                foreach ($cat_array as $cat) {

                    if ($cat==$i) {
                        $check_flag=true;
                        $key_val=1;
                    }
                }
            }
            if ($check_flag==true) {
                $subtotal += (int)$id[$size-1];
                $check_flag=false;
            }
        }
        if ($key_val==0):

            if (empty($key) || $key==0):

                $error= "<span style='color:#f00'>Sorry,
                Gift Card not available for this
                category/Categories</span>";

                $result=[0=>'5',1=>$error];
                return $result_return->setData($result);
            endif;
        endif;
    }

    /**
     * Quote totals update
     *
     * @param Quote $quote
     */
    private function updateTotalsInQuote(\Magento\Quote\Model\Quote $quote)
    {
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();
        $quote->setDataChanges(true);
        $this->quoteRepository->save($quote);

        return true;
    }

    /**
     * Return Categories
     */
    public function getCategories()
    {
        $items = $this->cart->getQuote()->getAllVisibleItems();
        $cat_ids = [];
        if ($items) {
            foreach ($items as $item) {
                $cat_id = "";
                foreach ($item->getProduct()->getCategoryIds() as $categoryid) {
                    if ($cat_id == "") {
                        $cat_id = $categoryid;
                    } else {
                        $cat_id = $cat_id.",".$categoryid;
                    }
                }
                $cat_ids[] = $cat_id;
            }
        }
        return $cat_ids;
    }
}
