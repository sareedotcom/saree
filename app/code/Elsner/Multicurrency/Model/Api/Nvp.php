<?php
namespace Elsner\Multicurrency\Model\Api;

use Magento\Payment\Model\Cart;
use Magento\Payment\Model\Method\Logger;

class Nvp extends \Magento\Paypal\Model\Api\Nvp
{
    protected $_helper;
    protected $_request;
    protected $_rounder;
    protected $_multicurrency;

    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Psr\Log\LoggerInterface $logger,
        Logger $customLogger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Paypal\Model\Api\ProcessableExceptionFactory $processableExceptionFactory,
        \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Elsner\Multicurrency\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Elsner\Multicurrency\Helper\Rounding $rounder,
        \Elsner\Multicurrency\Model\Multicurrency $multicurrency,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_rounder = $rounder;
        $this->_multicurrency = $multicurrency;
        parent::__construct($customerAddress, $logger, $customLogger, $localeResolver, $regionFactory, $countryFactory, $processableExceptionFactory, $frameworkExceptionFactory, $curlFactory, $data);
    }

    protected function _exportLineItemsCustom(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }

        $extrapricearray = array();
        // always add cart totals, even if line items are not requested
       /* echo "<pre>";
        print_r($this->_cart->getAmounts()); exit;*/
        if ($this->_lineItemTotalExportMap) {
            foreach ($this->_cart->getAmounts() as $key => $total) {
                if (isset($this->_lineItemTotalExportMap[$key])) {
                    
                    /*if($this->_helper->getEnableModule()){
                       // $total = $this->_helper->getConvertedAmount($total);    
                    }*/
                    
                    $privateKey = $this->_lineItemTotalExportMap[$key];
                    $request[$privateKey] = $this->formatPrice($total);

                    if($key != 'subtotal'){
                        $this->_rounder->addExtraPrice($key,$total);
                    }
                }
            }
        }
        
        // add cart line items
        $items = $this->_cart->getAllItems();
        if (empty($items) || !$this->getIsLineItemsEnabled()) {
            return;
        }

        $itempricearray = array();
        $result = null;
        foreach ($items as $item) {
            foreach ($this->_lineItemExportItemsFormat as $publicKey => $privateFormat) {
                $result = true;
                $value = $item->getDataUsingMethod($publicKey);
                if($publicKey == 'amount' && $this->_helper->getEnableModule() && is_object($item->getName()) !== true){
                   $value = $this->_helper->getConvertedAmount($value);
                }
               
                if($publicKey == 'qty'){
                    $this->_rounder->addItemPrice($i,'qty',$value);
                }
                if($publicKey == 'amount'){
                    $this->_rounder->addItemPrice($i,'amount',$value);
                }
                
                $request[sprintf($privateFormat, $i)] = $this->formatValue($value, $publicKey);
            }
            $i++;
        }
        
        //$result = $this->_rounder->convertRequest($request);
        return $result;
    }

    private function formatValue($value, $publicKey)
    {
        if (!empty($this->_lineItemExportItemsFilters[$publicKey])) {
            $callback = $this->_lineItemExportItemsFilters[$publicKey];
            $value = method_exists($this, $callback) ? $this->{$callback}($value) : $callback($value);
        }

        if (is_float($value)) {
            $value = $this->formatPrice($value);
        }

        return $value;
    }

    protected function _exportLineItems(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }
        $this->_cart->setTransferDiscountAsItem();
        return $this->_exportLineItemsCustom($request, $i);
    }
    
    public function callSetExpressCheckout()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        if($this->_helper->getEnableModule() && isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE'] != $this->_helper->getToCurrency()){
                $request['AMT'] = $this->_cart->getMulticurrencyTotal(); 
                $request['CURRENCYCODE'] = $this->_helper->getToCurrency();
                $multicurrencyObj = $this->_multicurrency->addRow($request['INVNUM'],$request['CURRENCYCODE'],'Authorize');
            }
        }
        $this->_exportLineItems($request);
        
        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 0;
        } elseif ($options && count($options) <= 10) {
            // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6;
            // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00;
            // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }
        /*echo "<pre>";
        print_r($request);
        exit;*/
        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }

    public function callDoExpressCheckoutPayment()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        if($this->_helper->getEnableModule() && isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE'] != $this->_helper->getToCurrency()){
                $request['AMT'] = $this->formatPrice($this->_helper->getConvertedAmount($request['AMT'])); 
                $request['CURRENCYCODE'] = $this->_helper->getToCurrency();
            }
            
        }
        $this->_exportLineItems($request);

        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 0;
        }
        
        $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
        $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
    }

    public function callRefundTransaction()
    {
        $request = $this->_exportToRequest($this->_refundTransactionRequest);
        if ($this->getRefundType() === \Magento\Paypal\Model\Config::REFUND_TYPE_PARTIAL) {
            $request['AMT'] = $this->formatPrice($this->getAmount());
        }
        if($this->_helper->getEnableModule() && isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE']){
                $detail = $this->_multicurrency->getRowByIncrementId($this->getPayment()->getOrder()->getIncrementId());
                if(empty($detail) !== true){
                    $request['AMT'] = $this->formatValue($this->_helper->getConvertedAmount($request['AMT'],$detail['paypal_currency_code']),'AMT'); 
                    $request['CURRENCYCODE'] = $detail['paypal_currency_code'];
                }
                
            }
        }
        $response = $this->call(self::REFUND_TRANSACTION, $request);
        $this->_importFromResponse($this->_refundTransactionResponse, $response);
    }

    public function callDoCapture()
    {
        $this->setCompleteType($this->_getCaptureCompleteType());
        $request = $this->_exportToRequest($this->_doCaptureRequest);
        if($this->_helper->getEnableModule() && isset($request['AMT']) && isset($request['CURRENCYCODE'])){
            if($request['CURRENCYCODE']){
                $detail = $this->_multicurrency->getRowByIncrementId($request['INVNUM']);
                if(empty($detail) !== true){
                    $request['AMT'] = $this->formatValue($this->_helper->getConvertedAmount($request['AMT'],$detail['paypal_currency_code']),'AMT'); 
                    $request['CURRENCYCODE'] = $detail['paypal_currency_code'];
                }
                
            }
        }

        $response = $this->call(self::DO_CAPTURE, $request);
       
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doCaptureResponse, $response);
    }

    public function callDoAuthorization()
    {
        $request = $this->_exportToRequest($this->_doAuthorizationRequest);
        
        if($this->_helper->getEnableModule() && isset($request['TRANSACTIONID'])){
            if($request['TRANSACTIONID']){
                $detail = $this->_multicurrency->getRowByTransection($request['TRANSACTIONID'])->getData();
                if(empty($detail) !== true){
                    $request['AMT'] = $this->formatValue($this->_helper->getConvertedAmount($request['AMT'],$detail['paypal_currency_code']),'AMT'); 
                    $request['CURRENCYCODE'] = $detail['paypal_currency_code'];
                }
                
            }
        }

        $response = $this->call(self::DO_AUTHORIZATION, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doAuthorizationResponse, $response);
        return $this;
    }
}
