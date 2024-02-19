<?php

namespace Mageants\GiftCard\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Currency extends \Elsnertech\CheckoutSwitcher\Controller\Index\Currency
{
    private const BASE_CURRENCY = 'USD';
    /**
     * @var \Magento\Framework\App\Action\Contex
     */
    private $context;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    private $currencyArr = [
        'US'=>'USD',
        'IN'=>'INR',
        'AE'=> 'AED',
        'AU'=> 'AUD',
        'CA'=> 'CAD',
        'SG'=> 'SGD',
        'GB'=> 'GBP',
        'AT'=> 'EUR',
        'CY'=> 'EUR',
        'CZ'=> 'EUR',
        'DK'=> 'EUR',
        'EE'=> 'EUR',
        'AT'=> 'EUR',
        'FR'=> 'EUR',
        'DE'=> 'EUR',
        'HU'=> 'EUR',
        'IE'=> 'EUR',
        'IT'=> 'EUR',
        'LV'=> 'EUR',
        'LT'=> 'EUR',
        'LU'=> 'EUR',
        'MT'=> 'EUR',
        'MC'=> 'EUR',
        'PL'=> 'EUR',
        'PT'=> 'EUR',
        'RO'=> 'EUR',
        'SK'=> 'EUR',
        'SI'=> 'EUR',
        'ES'=> 'EUR',
        'SE'=> 'EUR',
        'CH'=> 'EUR',
        'VA'=> 'EUR',
    ];

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->context           = $context;
        $this->storeManager      = $storeManager;
        $this->_checkoutSession  = $checkoutSession;
        parent::__construct($context,$storeManager);
    }
    
    /**
     * @return json
     */
    public function execute()
    {
        $this->_checkoutSession->setCurrentUrl('checkoutPage');

        $data = $this->context->getRequest()->getParams();
        if(!empty($data['country'])) {
            if(!isset($this->currencyArr[$data['country']])) {
                $currency = self::BASE_CURRENCY;
            }else{
                $currency = $this->currencyArr[$data['country']];
            }
            $currentCurrency = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            if($currentCurrency ==  $currency) {
                $resultJson->setData(["currency" => $currency, "changed" => false]);
            }else{
                $this->storeManager->getStore()->setCurrentCurrencyCode($currency);
                $resultJson->setData(["currency" => $currency, "changed" => true]);
            }
            return $resultJson;
        }
    }
}