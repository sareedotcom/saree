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

use Magento\Framework\DataObject\Copy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Pits\GiftWrap\Helper\Data as GiftWrapHelper;

/**
 * Class SaveOrderBeforeSalesModelQuoteObserver
 *
 * @package Pits\GiftWrap\Observer
 */
class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var GiftWrapHelper
     */
    private $giftWrapHelper;

    /**
     * SaveOrderBeforeSalesModelQuoteObserver constructor.
     *
     * @param Copy $objectCopyService
     * @param GiftWrapHelper $giftWrapHelper
     * @return void
     */
    public function __construct(
        Copy $objectCopyService,
        GiftWrapHelper $giftWrapHelper
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Copy Quote gift wrap data to Order table
     *
     * @param Observer $observer
     * @return SaveOrderBeforeSalesModelQuoteObserver
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        if ($this->giftWrapHelper->isModuleEnabled()) {
            $this->objectCopyService->copyFieldsetToTarget('sales_convert_quote', 'to_order', $quote, $order);
        }

        return $this;
    }
}
