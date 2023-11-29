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

namespace Pits\GiftWrap\Model\Invoice\Total;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Total\AbstractTotal;
use Pits\GiftWrap\Helper\Data;
use Pits\GiftWrap\Model\GiftWrapData;
use Pits\GiftWrap\Model\PriceCalculator;
use Psr\Log\LoggerInterface;

/**
 * Class Fee
 */
class Fee extends AbstractTotal
{
    /**
     * @var PriceCalculator
     */
    protected $priceCalculator;

    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $giftWrapHelper;

    /**
     * Fee constructor.
     *
     * @param PriceCalculator $priceCalculator
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param GiftWrapData $giftWrapData
     * @param Data $giftWrapHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        PriceCalculator $priceCalculator,
        RequestInterface $request,
        LoggerInterface $logger,
        GiftWrapData $giftWrapData,
        Data $giftWrapHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->priceCalculator = $priceCalculator;
        $this->giftWrapData = $giftWrapData;
        $this->request = $request;
        $this->logger = $logger;
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Collects invoice gift wrap fee totals.
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice): Fee
    {
        if ($this->giftWrapHelper->isModuleEnabled()) {
            $giftFee = $this->getGiftFee($invoice->getOrder(), true);
            $baseGiftFee = $this->getGiftFee($invoice->getOrder());
            $invoice->setGrandTotal($invoice->getGrandTotal() + $giftFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseGiftFee);
        }

        return $this;
    }

    /**
     * Get final gift fee
     *
     * @param Order $order
     * @param bool $store
     * @return float|int|mixed
     */
    public function getGiftFee(Order $order, $store = false)
    {
        $invoiceData = $this->request->getParam('invoice', []);
        $invoiceItems = $invoiceData['items'] ?? [];

        return $this->giftWrapData->getFinalInvoiceFee($order, $invoiceItems, $store);
    }
}
