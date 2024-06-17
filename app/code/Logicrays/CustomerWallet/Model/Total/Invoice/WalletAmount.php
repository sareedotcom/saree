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

namespace Logicrays\CustomerWallet\Model\Total\Invoice;

use \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use \Magento\Sales\Model\Order\Invoice;

class WalletAmount extends AbstractTotal
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
     * Add Walletamount in invoice summary
     *
     * @param Invoice $invoice
     * @return string
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();

        if ($order->getWalletamount()) {
            $baseWalletamount = $order->getWalletamount() * -1;
            $orderWalletamount = $order->getWalletamount();
            $walletamount = $this->_priceCurrency->convert($orderWalletamount);

            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()- $baseWalletamount);
            $invoice->setBaseWalletamount($walletamount);

            $invoice->setWalletamount(-$walletamount);
            $invoice->setGrandTotal($invoice->getGrandTotal() - $walletamount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $walletamount);

        }
        return $this;
    }
}
