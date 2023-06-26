<?php

namespace Elsner\Multicurrency\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ELSNER_MODULE_ENABLE = 'payment/multicurrency/active';
    const ELSNER_EXTRA_CURRENCY = 'payment/multicurrency/extra_currency';
    const ELSNER_CHECKOUT_CURRENCY = 'payment/multicurrency/to_currency';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $_order;
    
    /**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Sales\Api\Data\OrderInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->_storeManager = $storeManager;
        $this->_order = $order;
        parent::__construct($context);                  
    }

    /**
     * Check Module is enable or Not
     *
     * @return Boolean
     */
    public function getEnableModule() {

        return $this->scopeConfig->getValue(self::ELSNER_MODULE_ENABLE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Default Checkout Currency 
     *
     * @return currencyCode
     */
    public function getCheckoutDefaultCurrency() {

        $page = $this->scopeConfig->getValue(self::ELSNER_CHECKOUT_CURRENCY,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($page)?$page:'USD';
    }

    /**
     * List all the currency code supported by paypal
     *
     * @return Array
     */
    public static function getSupportedCurrency() {

        return array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
            'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB','INR');
    }

    /**
     * Check if request is convertable or not
     *
     * @return Boolean (True | False)
     */
    public static function shouldConvert() {

        return !self::isActive();
    }

    /**
     * Check Module is Active or Not
     *
     * @return Boolean (True | False)
     */
    public static function isActive() {

        $state = $this->getEnableModule();
        if (!$state) {
            return;
        }
        return $state;
    }

    /**
     * Get Checkout currency code
     * 
     * @return Currency Code
     */
    public function getToCurrency(){        
        $to = $this->getCheckoutDefaultCurrency();
        if (!$to){
            $to = 'USD';
        }

        $current_currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $getSupportedCurrency = $this->getSupportedCurrency();
        if(in_array($current_currency, $getSupportedCurrency)){  
            $to = $current_currency;        
        }
        return $to;
    }

    /**
     * Convert amount to specific currency
     *
     * @param (float) $amountValue
     * @param (string) $currencyCodeFrom
     * @param (string) $currencyCodeTo
     * @return (float) Converted Amount
     */
    public function convert($amountValue, $currencyCodeFrom = null, $currencyCodeTo = null) {

        return $this->_storeManager->getStore()->getBaseCurrency()->convert($amountValue ,$currencyCodeTo);
    }

    /**
     * Convert amount to Base currency
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return (float) Converted Base Amount
     */
    public function getConvertedBaseAmount($quote) {

        $toCur = $this->getToCurrency();
        $current_currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        if($toCur == $current_currency){
            return (float)$quote->getGrandTotal();
        }else{
            return (float)$this->getConvertedAmount($quote->getBaseGrandTotal());
        }       
        
    }

    /**
     * @param (float) $value
     * @return (float) Converted Amount
     */
    public function getConvertedAmount($value, $toCur = null) {

        $baseCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
        if($toCur === null){
            $toCur = $this->getToCurrency();   
        }
        $roundedvalue = $this->convert($value, $baseCode, $toCur);   
        return $roundedvalue;
    }

    /**
     * @return This function will return array of base currency.
     * array('SAR');
     */
    public function getCurrencyArray(){

        return array($this->_storeManager->getStore()->getBaseCurrencyCode());
    }

    /**
     * Load order by ID and get payment information
     *
     * @var $orderID
     * @return string
     */
    public function getPaymentCurrency($orderID){

        $order = $this->_order->load($orderID);
        if($order){
            $payment = $order->getPayment();
            return $payment->getAdditionalInformation('payment_currency');
        }
        return $this->getToCurrency();
    }

    /**
     * Get Store Config
     *
     * @param $identifier string
     * @return Store configuration value
     */
    public function getConfig($identifier){

        return $this->scopeConfig->getValue(
            $identifier,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
