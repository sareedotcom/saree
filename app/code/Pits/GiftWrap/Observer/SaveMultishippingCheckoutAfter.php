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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SaveMultishippingCheckoutAfter
 */
class SaveMultishippingCheckoutAfter implements ObserverInterface
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var Json
     */
    private $json;

    /**
     * SaveOrderBeforeSalesModelQuoteObserver constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $json
     * @return void
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Json $json
    ) {
        $this->orderRepository = $orderRepository;
        $this->json = $json;
    }

    /**
     * Copy Quote gift wrap data to Order table
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if($quote->getIsMultiShipping())
        {
            $orders = $observer->getEvent()->getOrders();
            $giftWrapData = $this->json->unserialize($quote->getGiftWrapData());
            foreach ($orders as $order) {
                $allCartGiftActive = false;
                $orderGiftwrapData = array();
                if($giftWrapData['whole_cart'])
                {
                    $allCartGiftActive = true;
                    $orderGiftwrapData['whole_cart'] = $giftWrapData['whole_cart'];
                }
                $ordeGiftwrapItemData = array();
                foreach ($order->getAllVisibleItems() as $item) {
                    foreach ($giftWrapData['items'] as $giftWrapItemid => $giftWrapItem) {
                        if($giftWrapItemid == $item->getQuoteItemId())
                        {
                            if(count($order->getAllVisibleItems()) > 1)
                            {
                                $ordeGiftwrapItemData[$giftWrapItemid] = $giftWrapItem;
                            }elseif(count($order->getAllVisibleItems()) == 1 && !$allCartGiftActive)
                            {
                                $orderGiftwrapData['whole_cart'] = $giftWrapItem;
                            }
                        }
                    }
                }
                $orderGiftwrapData['items'] = $ordeGiftwrapItemData;
                $order->setData('gift_wrap_data', $this->json->serialize($orderGiftwrapData));
                $this->orderRepository->save($order);
            }
        }
    }
}
