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

namespace Logicrays\CustomerWallet\Observer\OrderCancelled;

class RefundToWallet implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Logicrays\CustomerWallet\Model\CustomerWalletFactory
     */
    protected $customerWalletFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    protected $sendMail;

    /**
     * __construct function
     *
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     */
    public function __construct(
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Psr\Log\LoggerInterface $logger,
        \Logicrays\CustomerWallet\Model\Mail $sendMail
    ) {
        $this->helperData = $helperData;
        $this->customerWalletFactory = $customerWalletFactory;
        $this->logger = $logger;
        $this->sendMail = $sendMail;
    }

    /**
     * Execute function When order is canceld which is placed using wallet
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($this->helperData->refundInWallet()) {
            if ($order->getState() == 'canceled' && $order->getWalletamount()) {
                $walletamount = $order->getWalletamount();
                $data = [
                        'amount'  => $walletamount,
                        'name'  => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                        'email'  => $order->getCustomerEmail(),
                        'customer_id'  => $order->getCustomerId(),
                        'note'  => 'Refunded by '.$this->helperData->getAdminUser().' Order has been cancelled ',
                        'orderid'  => $order->getIncrementId(),
                        'status'  => '1',
                        'symbol' => $this->helperData->getCurrentCurrencySymbol()
                    ];
                try {
                    $model = $this->customerWalletFactory->create();
                    $model->setData($data);
                    $model->save();
                    $this->orderCancelRefundMail($data);
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                }
            }
        }
    }

    /**
     * Refund mail for order cancel
     *
     * @param array $data
     * @return mixed
     */
    public function orderCancelRefundMail($data)
    {
        if ($this->helperData->sendEmailEnabled()) {
            $this->sendMail->send(
                $data,
                $this->helperData->receiverMoneyTemplate(),
                $data['email'],
            );
        }
    }
}
