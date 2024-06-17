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

class WalletRequestOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * CustomerWalletFactory variable
     *
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    protected $customerWalletFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cartItems;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    protected $sendMail;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * __construct function
     *
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Magento\Checkout\Model\Cart $cartItems
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Magento\Checkout\Model\Cart $cartItems,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\Mail $sendMail,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerWalletFactory = $customerWalletFactory;
        $this->cartItems = $cartItems;
        $this->helperData = $helperData;
        $this->sendMail = $sendMail;
        $this->quoteFactory = $quoteFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Execute function to save data after place order
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');

        $quote = $this->cartItems->getQuote();

        // saving wallet debit request when it use and place order with wallet
        if ($quote->getWalletamount() != null && $quote->getWalletamount() != 0) {
            $order->setWalletamount($quote->getWalletamount());
            $walletAmountUsed = $quote->getWalletamount();

            // order comment history
            $comment = 'Wallet amount '.$walletAmountUsed.' used for Order';
            $order->addStatusHistoryComment($comment);
            $order->save();

            $data = [
                    'amount'  => $walletAmountUsed,
                    'name'  => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                    'email'  => $order->getCustomerEmail(),
                    'customer_id'  => $order->getCustomerId(),
                    'note'  => 'Used for Order ',
                    'status'  => '4',
                    'orderid' => $order->getIncrementId()
                ];
            $model = $this->customerWalletFactory->create();
            $model->setData($data);
            $model->save();

            $init_status = 'Debited';
            $data['init_status'] = $init_status;
            $data['prefix_mail_note'] = 'You have used ';
            $data['suffix_mail_note'] = 'Amount debited from your Wallet. ';
            $data['amount'] = $this->helperData->getCurrentCurrencySymbol() . $walletAmountUsed;

            if ($this->helperData->sendEmailEnabled()) {
                $this->sendMail->send(
                    $data,
                    $this->helperData->customerEmailTemplate(),
                    $data['email'],
                );
            }
        }

        // add money to wallet request send
        $allItems = $this->cartItems->getQuote()->getAllItems();
        foreach ($allItems as $item) {
            // check item sku is same as config setting for money request
            if ($item->getSku() == $this->helperData->walletSKU()) {

                // add order comment history
                $comment = 'Money added into wallet '.$order->getBaseGrandTotal();
                $order->addStatusHistoryComment($comment);
                $order->save();

                $status = 0;
                $init_status = 'Pending';
                if ($order->hasInvoices()) {
                    $status = 1;
                    $init_status = 'Approved';
                }

                $noteMoneyAddtoWallet = 'Added Money into Wallet ';
                if ($this->helperData->getTextAddedMoneyToWallet()) {
                    $noteMoneyAddtoWallet = $this->helperData->getTextAddedMoneyToWallet();
                }
                $data =
                    [
                        'amount'  => $order->getBaseGrandTotal(),
                        'name'  => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                        'note'  => $noteMoneyAddtoWallet,
                        'email'  => $order->getCustomerEmail(),
                        'customer_id'  => $order->getCustomerId(),
                        'init_status'  => $init_status,
                        'symbol' => $this->helperData->getCurrentCurrencySymbol(),
                        'status'  => $status,
                        'orderid' => $order->getIncrementId()
                    ];
                $model = $this->customerWalletFactory->create();
                $model->setData($data);
                $model->save();

                if ($this->helperData->sendEmailEnabled()) {
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
        $this->createEmptyQuoteForCustomer();
        return $this;
    }

    /**
     * Create Empty Quote For Customer function
     *
     * @return array
     */
    public function createEmptyQuoteForCustomer()
    {
        // Get the customer ID from the customer session
        $customerId = $this->customerSession->getCustomerId();
        // Create a new quote for the customer
        $quote = $this->quoteFactory->create();
        $quote->setCustomerId($customerId);
        $quote->setStoreId($quote->getStore()->getStoreId());
        $quote->setIsActive(true);
        $quote->setIsMultiShipping(false);
        $quote->save();
        return $quote;
    }
}
