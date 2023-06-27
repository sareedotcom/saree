<?php

namespace Logicrays\OrderDeliveryEstimation\Plugin\Payment\Method\CashOnDelivery;

use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Checkout\Model\Cart;

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
     * @param BackendSession $backendSession
     * @param Cart $cartModel
     */
    public function __construct(
        BackendSession $backendSession,
        Cart $cartModel
    ) {
        $this->backendSession = $backendSession;
        $this->_cartModel = $cartModel;
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
        foreach ($cartAllItems as $item) {
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            if (isset($options['options'])) {
                $customOptions = $options['options'];
                if (!empty($customOptions)) {
                    foreach ($customOptions as $option) {
                        $optionValue = $option['value'];
                        if (!empty($optionValue) && $optionValue == 'Measurements') {
                            $flag = 1;
                        }
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
