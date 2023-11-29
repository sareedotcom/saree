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

namespace Pits\GiftWrap\Model\Creditmemo\Total;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Pits\GiftWrap\Helper\Data;
use Pits\GiftWrap\Model\GiftWrapData;

/**
 * Class Fee
 *
 * @package Pits\GiftWrap\Model\Creditmemo\Total
 */
class Fee extends AbstractTotal
{
    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $giftWrapHelper;

    /**
     * Fee constructor.
     *
     * @param RequestInterface $request
     * @param GiftWrapData $giftWrapData
     * @param Data $giftWrapHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        RequestInterface $request,
        GiftWrapData $giftWrapData,
        Data $giftWrapHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->giftWrapData = $giftWrapData;
        $this->request = $request;
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Collects creditmemo gift wrap fee totals.
     *
     * @param Creditmemo $creditmemo
     * @return Fee
     */
    public function collect(Creditmemo $creditmemo): Fee
    {
        if ($this->giftWrapHelper->isModuleEnabled()) {
            $giftFee = $this->getGiftFee($creditmemo->getOrder(), true);
            $baseGiftFee = $this->getGiftFee($creditmemo->getOrder());
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $giftFee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseGiftFee);
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
        $creditmemoData = $this->request->getParam('creditmemo', []);
        $creditmemoItems = $creditmemoData['items'] ?? [];

        return $this->giftWrapData->getFinalRefundFee($order, $creditmemoItems, $store);
    }
}
