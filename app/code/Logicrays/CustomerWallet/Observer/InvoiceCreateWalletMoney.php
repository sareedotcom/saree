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

namespace Logicrays\CustomerWallet\Observer;

class InvoiceCreateWalletMoney implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    protected $customerWalletFactory;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    protected $sendMail;

    /**
     * __construct function
     *
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     */
    public function __construct(
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\Mail $sendMail
    ) {
        $this->customerWalletFactory = $customerWalletFactory;
        $this->helperData = $helperData;
        $this->sendMail = $sendMail;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();

        $order = $invoice->getOrder();
        $orderIncrementId = $order->getIncrementId();

        $walletCollection = $this->customerWalletFactory->create()->load($orderIncrementId, 'orderid');

        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            if (!$walletCollection->getStatus() && $item->getSku() == $this->helperData->walletSKU()) {
                // order comments
                $comment = 'Amount '.$order->getBaseGrandTotal().' Credited in wallet';
                $order->addStatusHistoryComment($comment);
                $order->save();

                $walletCollection->setStatus(1);
                $walletCollection->save();
                $this->sendCreditedMail($order);
            }
        }
    }

    /**
     * Function to send mail
     *
     * @param mixed $order
     * @return mixed
     */
    public function sendCreditedMail($order)
    {
        if ($this->helperData->sendEmailEnabled()) {
            $data =
                [
                    'amount'  => $order->getBaseGrandTotal(),
                    'name'  => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                    'note'  => 'Added Money into Wallet',
                    'email'  => $order->getCustomerEmail(),
                    'init_status'  => 'Credited',
                    'symbol' => $this->helperData->getCurrentCurrencySymbol()
                ];

            // send mail to admin or store owner of add money into wallet request
            if (!empty($this->helperData->adminEmail()) && $this->helperData->sendAdminMailEnabled()) {
                $this->sendMail->send(
                    $data,
                    $this->helperData->adminEmailTemplate(),
                    $this->helperData->adminEmail(),
                );
            }
            // send mail to customer of add money into wallet request
            $this->sendMail->send(
                $data,
                $this->helperData->customerEmailTemplate(),
                $data['email'],
            );
        }
    }
}
