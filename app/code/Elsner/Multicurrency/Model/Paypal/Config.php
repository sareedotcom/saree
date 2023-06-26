<?php

namespace Elsner\Multicurrency\Model\Paypal;

class Config extends \Magento\Paypal\Model\Config {

    protected $_helper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Source\CctypeFactory $cctypeFactory,
        \Magento\Paypal\Model\CertFactory $certFactory,
        \Elsner\Multicurrency\Helper\Data $helper,
        $params = []
    ) {
        parent::__construct($scopeConfig, $directoryHelper, $storeManager, $cctypeFactory, $certFactory, $params);
        $this->_helper = $helper;
    }
	
	protected $_supportedCurrencyCodes = array(
		'AUD',
        'CAD',
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'JPY',
        'MXN',
        'NOK',
        'NZD',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'TWD',
        'THB',
        'USD',
    );

    public function isCurrencyCodeSupported($code)
    {

    	if($this->_helper->getEnableModule()){
            
    		$this->_supportedCurrencyCodes = array_merge($this->_supportedCurrencyCodes, $this->_helper->getCurrencyArray());
    	}

        if (in_array($code, $this->_supportedCurrencyCodes)) {
            return true;
        }
        if ($this->getMerchantCountry() == 'BR' && $code == 'BRL') {
            return true;
        }
        if ($this->getMerchantCountry() == 'MY' && $code == 'MYR') {
            return true;
        }
        if ($this->getMerchantCountry() == 'TR' && $code == 'TRY') {
            return true;
        }
        return false;
    }
}
	
	