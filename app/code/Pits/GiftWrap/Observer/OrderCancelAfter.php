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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Pits\GiftWrap\Model\GiftWrapData;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class OrderCancelAfter
 */
class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * OrderCancelAfter constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param GiftWrapData $giftWrapData
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        GiftWrapData $giftWrapData
    ) {
        $this->orderRepository = $orderRepository;
        $this->giftWrapData = $giftWrapData;
    }

    /**
     * Execute method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $cancelledFee = $this->giftWrapData->getOrderCancelledGiftFee($order);
        $order->setData(Wrap::GIFT_WRAP_FEE_CANCELED_IDENTIFIER, $cancelledFee);
        $this->orderRepository->save($order);
    }
}
