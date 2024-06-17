<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

class PaymentMethods extends DataObject implements OptionSourceInterface
{
    /**
     * _appConfigScopeConfigInterface variable
     *
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * _paymentHelper variable
     *
     * @var Data
     */
    protected $paymentHelper;

    /**
     * __construct function
     *
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Data $paymentHelper
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Data $paymentHelper
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentHelper = $paymentHelper;
    }

    /**
     * ToOptionArray function
     *
     * @return array
     */
    public function toOptionArray()
    {
        // $payments = $this->_paymentModelConfig->getActiveMethods();
        $payments = $this->_paymentHelper->getPaymentMethods();

        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            if(isset($paymentModel['active']) && isset($paymentModel['title'])){
                $methods[$paymentCode] = [
                    'label' => $paymentModel['title'],
                    'value' => $paymentCode
                ];
            }
        }
        
        return $methods;
    }
}
