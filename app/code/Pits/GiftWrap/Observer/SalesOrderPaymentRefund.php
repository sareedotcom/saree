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
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Pits\GiftWrap\Model\GiftWrapData;
use Psr\Log\LoggerInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class SalesOrderPaymentRefund
 *
 * @package Pits\GiftWrap\Observer
 */
class SalesOrderPaymentRefund implements ObserverInterface
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
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * SalesOrderPaymentRefund constructor.
     *
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param RequestInterface $request
     * @param GiftWrapData $giftWrapData
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @return void
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        RequestInterface $request,
        GiftWrapData $giftWrapData,
        CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->giftWrapData = $giftWrapData;
        $this->creditmemoRepository = $creditmemoRepository;
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
            /** @var Creditmemo $creditmemo */
            $creditmemo = $observer->getEvent()->getData('creditmemo');
            $order = $creditmemo->getOrder();
            $creditmemoData = $this->request->getParam('creditmemo', []);
            $creditmemoItems = $creditmemoData['items'] ?? [];
            $fee = $this->giftWrapData->getFinalRefundFee($order, $creditmemoItems, true);
            $baseFee = $this->giftWrapData->getFinalRefundFee($order, $creditmemoItems);
            $creditmemo->setData(Wrap::GIFT_WRAP_FEE_IDENTIFIER, $fee);
            $creditmemo->setData(Wrap::BASE_GIFT_WRAP_FEE_IDENTIFIER, $baseFee);
            $this->creditmemoRepository->save($creditmemo);
            $fee += $order->getGiftWrapRefunded() ?? 0;
            $order->setData(Wrap::GIFT_WRAP_FEE_REFUNDED_IDENTIFIER, $fee);
            $order->setData(Wrap::BASE_GIFT_WRAP_FEE_REFUNDED_IDENTIFIER, $baseFee);
            $this->orderRepository->save($order);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
