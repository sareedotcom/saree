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

use Magento\Sales\Model\Order;

class RefundInWallet implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Logicrays\CustomerWallet\Model\Mail
     */
    protected $sendMail;

    /**
     * __construct function
     *
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory
     * @param \Logicrays\CustomerWallet\Model\Mail $sendMail
     */
    public function __construct(
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Logicrays\CustomerWallet\Model\CustomerWalletFactory $customerWalletFactory,
        \Logicrays\CustomerWallet\Model\Mail $sendMail
    ) {
        $this->helperData = $helperData;
        $this->customerWalletFactory = $customerWalletFactory;
        $this->sendMail = $sendMail;
    }

    /**
     * Execute when refund in wallet in enabled function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return float
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($this->helperData->refundInWallet()) {

            /** @var \Magento\Sales\Model\Order $order */
            $order = $creditmemo->getOrder();

            $refundAmount = $order->getBaseGrandTotal();
            $data = [
                    'amount'  => $refundAmount,
                    'name'  => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                    'email'  => $order->getCustomerEmail(),
                    'customer_id'  => $order->getCustomerId(),
                    'note'  => 'Refunded by '.$this->helperData->getAdminUser().' (CreditMemo) for Order ',
                    'orderid'  => $order->getIncrementId(),
                    'status'  => '1',
                    'symbol' => $this->helperData->getCurrentCurrencySymbol()
                ];
            $model = $this->customerWalletFactory->create();
            $model->setData($data);
            $model->save();
            $this->orderCreditMemoRefundMail($data);

            $order->setState(Order::STATE_CLOSED)->setStatus($order->getConfig()
            ->getStateDefaultStatus(Order::STATE_CLOSED));
            $order->save();
        }
        return $this;
    }

    /**
     * Refund mail for order Credit memo
     *
     * @param array $data
     * @return mixed
     */
    public function orderCreditMemoRefundMail($data)
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
