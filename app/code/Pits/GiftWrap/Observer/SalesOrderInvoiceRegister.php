<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

namespace Pits\GiftWrap\Observer;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Pits\GiftWrap\Model\GiftWrapData;
use Psr\Log\LoggerInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class SalesOrderInvoiceRegister
 *
 * @package Pits\GiftWrap\Observer
 */
class SalesOrderInvoiceRegister implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * SalesOrderInvoiceRegister constructor.
     *
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestInterface $request
     * @param GiftWrapData $giftWrapData
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        RequestInterface $request,
        GiftWrapData $giftWrapData,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->giftWrapData = $giftWrapData;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();
            /** @var Invoice $invoice */
            $invoice = $observer->getEvent()->getInvoice();
            $invoiceData = $this->request->getParam('invoice', []);
            $invoiceItems = $invoiceData['items'] ?? [];
            $fee = $this->giftWrapData->getFinalInvoiceFee($order, $invoiceItems, true);
            $baseFee = $this->giftWrapData->getFinalInvoiceFee($order, $invoiceItems);
            $invoice->setData(Wrap::GIFT_WRAP_FEE_IDENTIFIER, $fee);
            $invoice->setData(Wrap::BASE_GIFT_WRAP_FEE_IDENTIFIER, $baseFee);
            $this->invoiceRepository->save($invoice);
            $fee += $order->getGiftWrapInvoiced() ?? 0;
            $order->setData(Wrap::GIFT_WRAP_FEE_INVOICED_IDENTIFIER, $fee);
            $order->setData(Wrap::BASE_GIFT_WRAP_FEE_INVOICED_IDENTIFIER, $baseFee);
            $this->orderRepository->save($order);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
