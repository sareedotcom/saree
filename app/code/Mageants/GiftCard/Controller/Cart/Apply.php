<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Cart;

use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session;
use \Mageants\GiftCard\Helper\Data;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\App\Request\Http;
use \Magento\Checkout\Model\Cart;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Account;

/**
 * Apply the Gift code in checkout page
 */

class Apply extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $category;
    
    /**
     * @var Account
     */
    public $account;

    /**
     * @var Codelist
     */
    public $codelist;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Http
     */
    public $request;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     * @param OrderFactory $orderFactory
     * @param Http $request
     * @param Cart $cart
     * @param Codelist $codelist
     * @param Account $account
     * @param Magento\Catalog\Model\CategoryFactory $category
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Data $helper,
        JsonFactory $resultJsonFactory,
        OrderFactory $orderFactory,
        Http $request,
        Cart $cart,
        Codelist $codelist,
        Account $account,
        \Magento\Catalog\Model\CategoryFactory $category
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->request = $request;
        $this->cart = $cart;
        $this->codelist = $codelist;
        $this->account = $account;
        $this->orderFactory = $orderFactory;
        $this->category = $category;
        parent::__construct($context);
    }

    /**
     * Perform Apply Action
     */
    public function execute()
    {
        $result_return = $this->resultJsonFactory->create();
        $this->_checkoutSession->unsGift();

        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            $catids = $data['categoryids'];
            $quote = $this->cart;

            $gift_card_subtotal = '0';
            if ($quote->getItems()->count() > 1) {
                foreach ($quote->getItems()->getData() as $key => $value) {
                    if ($value['product_type'] == 'giftcertificate') {
                        $gift_card_subtotal += $value['row_total'];
                    }
                }
            }
            $subtotal = 0;

            $cart = $this->cart;
            $totals = $cart->getQuote()
            ->getTotals();

            $cart_subtotal = $totals['subtotal']['value'] - $gift_card_subtotal;

            $gifCodes = $this->codelist;

            $availableCode = $this->account->getCollection()
            ->addFieldToFilter('gift_code', trim($data['gift_code']))
            ->addFieldToFilter('status', 1);

            if (empty($availableCode->getData())):
                $error = "<span style='color:#f00'>Invalid Gift Card</span>";
                $result = [0 => '1',1 => $error];
                return $result_return->setData($result);
            else:
                $cat_array = [];
                foreach ($availableCode as $catlist) {
                    $cat_array = explode(",", $catlist->getCategories());
                }
                foreach ($availableCode->getData() as $code) {
                    $orderIncrementId = $code['order_increment_id'];
                    $order = $this
                     ->orderFactory
                     ->create()
                     ->loadByIncrementId($orderIncrementId);
                    if ($order->getStatus() == "canceled" || $order->getStatus() == "closed") {
                        $error = "<span style='color:#f00'>Invalid Gift Card Code</span>";

                        $result = [
                         0 => '5',
                         1 => $error
                        ];
                        return $result_return->setData($result);
                    }
                }
    
                return $this->getCart(
                    $cat_array,
                    $catids,
                    $availableCode,
                    $subtotal,
                    $gift_card_subtotal,
                    $cart,
                    $result_return
                );

            endif;
        }
    }

    /**
     * Return to product detail in cart
     *
     * @param array $cat_array
     * @param string $catids
     * @param object $availableCode
     * @param int $subtotal
     * @param int $gift_card_subtotal
     * @param int $cart
     * @param object $result_return
     */
    public function getCart($cat_array, $catids, $availableCode, $subtotal, $gift_card_subtotal, $cart, $result_return)
    {
        if (!empty($cat_array)):
            foreach ($catids as $key => $value) {
                $resultArray = explode(',', trim($value));
            }
            foreach ($catids as $catid => $cat) {
                $category = $this->category->create()->load(trim($cat));
            }

            $category = $category->getParentIds();
            $resultArray = array_merge($resultArray, $category);
            $data = array_intersect($resultArray, $cat_array);

            if (!$data) {
                $cat_array_string = implode(" ",$cat_array); 
                
                for ($i = 0; $i < count($catids); $i++) {
                    $catids_new[$i] = str_replace(' ', '', $catids[$i]);
                    $catids_array = explode(",", $catids_new[$i]); 

                    if (in_array($cat_array_string, $catids_array)) {
                        $this->cartDetail($catids, $cat_array, $subtotal, $result_return);
                    } else {
                        $error = "<span style='color:#f00'>Sorry,
                        Gift Card not available for this category/Categories</span>";
                        $result = [
                            0 => '5',
                            1 => $error
                        ];
                        return $result_return->setData($result);
                    }  
                }                
            } else {
                $this->cartDetail($catids, $cat_array, $subtotal, $result_return);
            }
        endif;
           $certificate_value = 0;

        foreach ($availableCode as $code) {

            if ($code->getCurrentBalance() == 0):

                $error = "<span style='color:black'>You Don't have enough balance.</span>";

                $result = [
                 0 => '2',
                 1 => $error
                ];

                return $result_return->setData($result);

            endif;

            if (!$this
             ->_helper
             ->allowSelfUse()):

                if ($code->getCustomerId() == $this
                 ->_helper
                 ->getCustomerId()):

                    $error = "<span style='color:#f00'>Sorry, You cannot use certificate for yourself</span>";

                    $result = [
                     0 => '4',
                     1 => $error
                    ];

                    return $result_return->setData($result);

                endif;

            endif;

            if ($code->getExpireAt() != '0000-00-00' && $code->getExpireAt() != '1970-01-01'):

                $currentDate = date('Y-m-d');

                if ($currentDate > $code->getExpireAt()):

                    $error = "<span style='color:#f00'>Sorry, This Gift Card Has Been Expired</span>";

                    $result = [
                     0 => '4',
                     1 => $error
                    ];

                    return $result_return->setData($result);

                endif;

            endif;

            $certificate_value = $code->getAllowBalance();
            $currentbal = $code->getCurrentBalance();
            $percentbal = $code->getPercentage();

            if ($certificate_value == '' && $percentbal == '') {
                $error = "<span style='color:#f00'>Please wait for admin approval</span>";

                $result = [
                 0 => '4',
                 1 => $error
                ];

                return $result_return->setData($result);
            }
            if ($currentbal < $certificate_value) {
                $certificate_value = $currentbal;
            }
            
            $quote = $this->cart;
            $totals = $cart->getQuote()
             ->getTotals();

            $cart_subtotal = $totals['subtotal']['value'] - $gift_card_subtotal;

            $gift_value = (int) $cart_subtotal;

            if ($certificate_value < (int)$cart_subtotal) {
                $gift_value = $certificate_value;
            }
            $action = $this
             ->request
             ->getFullActionName();

            $accund_id = $code->getAccountId();

            $updateblance = $code->getCurrentBalance() - $gift_value;

            if ($code->getDiscountType() == "percent") {

                $certificate_value = $code->getPercentage();

                if ($certificate_value > 100) {
                    $discount = 1;
                } else {
                    $discount = $certificate_value;
                }

                $gift_value = ($code['initial_code_value'] * $discount) / 100;

                if ($gift_value > (int)$cart_subtotal) {
                    $gift_value = (int)$cart_subtotal;
                } elseif ($gift_value > $code->getCurrentBalance()) {
                    $gift_value = $code->getCurrentBalance();
                    
                }

                $updateblance = $code->getCurrentBalance() - $gift_value;
            }

            $result = [
             0 => '3',
             1 => 'Gift Card Accepted'
            ];

            $result_return->setData($result);
            //$_SESSION['custom_gift'] = $gift_value;

            $this
             ->_checkoutSession
             ->setGift($gift_value);

            $this
             ->_checkoutSession
             ->setGiftCardCode($code->getGiftCode());

            $this
             ->_checkoutSession
             ->setAccountid($accund_id);

            $this
             ->_checkoutSession
             ->setGiftbalance($updateblance);

            $this
             ->_checkoutSession
             ->getQuote()
             ->collectTotals()
             ->save();
            $cartQuote = $quote->getQuote();
            $cartQuote->getShippingAddress()
             ->setCollectShippingRates(true);
            return $result_return;

        }
    }

    /**
     * Return to product detail in cart
     *
     * @param string $catids
     * @param array $cat_array
     * @param int $subtotal
     * @param object $result_return
     */
    public function cartDetail($catids, $cat_array, $subtotal, $result_return)
    {
        $key_val = 0;
        $check_flag = false;
        foreach ($catids as $catid) {

            $id = explode(",", $catid);
            $size = count($id);

            foreach ($id as $i) {

                foreach ($cat_array as $cat) {

                    if ($cat == $i) {
                        $check_flag = true;
                        $key_val = 1;
                    }
                }
            }
            if ($check_flag == true) {

                $subtotal += (int)$id[$size - 1];

                $check_flag = false;
            }
        }
        if ($key_val == 0):

            if (empty($key) || $key == 0):

                $error = "<span style='color:#f00'>Sorry,
                Gift Card not available for this category/Categories</span>";

                $result = [
                0 => '5',
                1 => $error
                ];
                return $result_return->setData($result);
            endif;
        endif;
    }
}
