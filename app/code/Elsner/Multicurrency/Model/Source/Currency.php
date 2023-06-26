<?php
namespace Elsner\Multicurrency\Model\Source;

class Currency extends \Magento\Config\Model\Config\Source\Locale\Currency
{
    protected $_localeLists;
    protected $_currencySymbol;
    protected $_helper;

    public function __construct(
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Store\Model\StoreManagerInterface $currencySymbol,
        \Elsner\Multicurrency\Helper\Data $helper
        )
    {
        $this->_localeLists = $localeLists;
        $this->_currencySymbol = $currencySymbol;
        $this->_helper = $helper;
        parent::__construct($localeLists);  
    }

    public function toOptionArray()
    {
        $_supportedCurrencyCodes = $this->_helper->getSupportedCurrency();
        $_availableCurrencyCodes = $this->_currencySymbol->getStore()->getAvailableCurrencyCodes(true);;
        if (!$this->_options) {
            $this->_options = $this->_localeLists->getOptionCurrencies();
        }
        $options = array();
        foreach ($this->_options as $option) {
            if (in_array($option['value'], $_supportedCurrencyCodes) && in_array($option['value'], $_availableCurrencyCodes)) {
                $options[] = $option;
           }
        }
       //echo '<pre>';print_r($options);exit;
        return $options;
    }
}