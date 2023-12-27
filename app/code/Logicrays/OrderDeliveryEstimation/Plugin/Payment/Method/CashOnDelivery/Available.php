<?php

namespace Logicrays\OrderDeliveryEstimation\Plugin\Payment\Method\CashOnDelivery;

use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Available
{
    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var Cart
     */
    protected $_cartModel;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param BackendSession $backendSession
     * @param Cart $cartModel
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BackendSession $backendSession,
        Cart $cartModel,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->backendSession = $backendSession;
        $this->_cartModel = $cartModel;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Checking COD payment method
     *
     * @param Cashondelivery $subject
     * @param [type] $result
     * @return bool
     */
    public function afterIsAvailable(Cashondelivery $subject, $result)
    {
        if ($this->backendSession->isLoggedIn()) {
            return $result;
        }
        $quote = $this->_cartModel->getQuote();
        $cartAllItems = $quote->getItems();
        $flag= 0;
        $isEnabled = $this->scopeConfig->getValue('cod_disable/general/enabled');
        $enabledCategories = $this->scopeConfig->getValue('cod_disable/general/enabled_categories');
        foreach ($cartAllItems as $item) {
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            if (isset($options['options'])) {
                $customOptions = $options['options'];
                if (!empty($customOptions)) {
                    foreach ($customOptions as $option) {
                        $optionValue = $option['value'];
                        if (!empty($optionValue) && ($optionValue == 'Measurements' || $optionValue == 'Later')) {
                            $flag = 1;
                        }
                    }
                }
            }
            if ($isEnabled && $enabledCategories && $flag != 1) {
                if($enabledCategories){
                    $enabledCategories1 = explode(",",$enabledCategories);
                    $categories = $item->getProduct()->getCategoryIds();
                    if (count(array_intersect($categories, $enabledCategories1)) > 0) {
                        $flag = 1;
                        return false;
                    }
                }
            }
        }
        if ($flag == 1) {
            return false;
        }
        return $result;
    }
}
