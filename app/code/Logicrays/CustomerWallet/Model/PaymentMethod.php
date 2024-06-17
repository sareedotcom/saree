<?php
namespace Logicrays\CustomerWallet\Model;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'walletpayment';

    /**
     * _canUseCheckout variable
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * IsAvailable function
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return boolean
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote->getWalletamount() <= '0' || $quote->getWalletamount() != abs($quote->getGrandTotal()) || $quote->getGrandTotal() == '0') {
            return false;
        }
        return true;
    }

    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }
}
