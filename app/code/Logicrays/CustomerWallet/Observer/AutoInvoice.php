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

class AutoInvoice implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Sales\Model\Order $order
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\Order $order,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->order = $order;
        $this->helperData = $helperData;
    }

    /**
     * Execute function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');

        if ($this->helperData->enableAutoInvoice() && $order->getGrandTotal() == 0) {
            $this->createAutoInvoice($observer->getEvent()->getOrder()->getId());
        }
    }

    /**
     * Create Auto Invoiced function
     *
     * @param int $orderId
     * @return mixed
     */
    public function createAutoInvoice($orderId)
    {
        $order = $this->order->load($orderId);

        $invoice = $this->invoiceService->prepareInvoice($order);

        $invoice->register();
        $invoice->save();

        $orderCommentHistory = 'Invoice has been created, Order placed using wallet.';
        if ($this->helperData->enableAutoInvoice()) {
            $orderCommentHistory = $this->helperData->autoInvoiceOrderComment();
        }
        // Add a comment to the order status history
        $order->addStatusHistoryComment($orderCommentHistory)
        ->setIsCustomerNotified(true)
        ->save();
        // Update the order's state and status to 'complete'
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->save();

        // Send the invoice email to the customer
        $this->invoiceSender->send($invoice);
    }
}
