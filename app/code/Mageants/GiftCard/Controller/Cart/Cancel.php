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
use \Magento\Checkout\Model\Cart;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Account;

/**
 * Apply the Gift code in checkout page
 */

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Account
     */
    public $account;

    /**
     * @var Codelist
     */
    public $codelist;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */

    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param Data $helper
     * @param Cart $cart
     * @param Codelist $codelist
     * @param Account $account
     * @param JsonFactory $resultJsonFactory
     */

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Data $helper,
        Cart $cart,
        Codelist $codelist,
        Account $account,
        JsonFactory $resultJsonFactory
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession=$checkoutSession;
        $this->_cart = $cart;
        $this->_helper=$helper;
        $this->codelist = $codelist;
        $this->account = $account;
        parent::__construct($context);
    }
    
    /**
     * Perform Apply Action
     */
    public function execute()
    {
        $result_return = $this->resultJsonFactory->create();
        $this->_checkoutSession->unsGift();

        $data=$this->getRequest()->getPostValue();
        if (!empty($data)) {
            $catids=$data['categoryids'];

            $subtotal=0;

            $gifCodes = $this->codelist;

            $availableCode = $this->account->getCollection()
            ->addFieldToFilter('gift_code', trim($data['gift_code']))
            ->addFieldToFilter('status', 1);
        
            $certificate_value=0;

            foreach ($availableCode as $code) {

                $certificate_value=  $code->getCurrentBalance();

                 $quote = $this->_cart->getQuote();

                 $gift_value=$this->_checkoutSession->getGift();

                   $accund_id=$code->getAccountId();

                 $updateblance=$code->getCurrentBalance() + $gift_value;

                 $result=[0=>'3',1=>'Gift Card Cancelled'];
                 $result_return->setData($result);

                // $_SESSION['custom_gift'] = $gift_value;

                $this->_checkoutSession->setGift(0);
                $this->_checkoutSession->setGiftCardCode("");

                $this->_checkoutSession->setAccountid($accund_id);

                $this->_checkoutSession->setGiftbalance($updateblance);
                $this->_checkoutSession->getQuote()->collectTotals()->save();

                return $result_return;

            }
        }
    }
}
