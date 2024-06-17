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

namespace Logicrays\CustomerWallet\Model\Total\Quote;

class WalletAmount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency [description]
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->_priceCurrency = $priceCurrency;
    }

    /**
     * Collect Totals function
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return mixed
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
            $baseWalletamount = $quote->getWalletamount() * -1;
            $quoteWalletamount = $quote->getWalletamount();
            $walletamount = $this->_priceCurrency->convert($quoteWalletamount);
            $walletamount = $walletamount * -1;

            $total->addTotalAmount($this->getCode(), $walletamount);
            $total->addBaseTotalAmount($this->getCode(), $baseWalletamount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal()- $baseWalletamount);
            $total->setBaseWalletamount($walletamount);

            // $walletamount = $quote->getWalletamount() * -1;
            // $total->setTotalAmount($this->getCode(), $walletamount);
            // $total->setBaseTotalAmount($this->getCode(), $walletamount);
            // $total->setWalletamount($walletamount);
            // $total->setBaseWalletamount($walletamount);
        return $this;
    }
}
