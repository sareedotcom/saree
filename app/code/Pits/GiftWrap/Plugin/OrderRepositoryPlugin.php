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

namespace Pits\GiftWrap\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class OrderRepositoryPlugin
 *
 * @package Pits\GiftWrap\Plugin
 */
class OrderRepositoryPlugin
{
    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * OrderRepositoryPlugin constructor.
     *
     * @param OrderExtensionFactory $extensionFactory
     * @param Wrap $wrap
     * @return void
     */
    public function __construct(OrderExtensionFactory $extensionFactory, Wrap $wrap)
    {
        $this->extensionFactory = $extensionFactory;
        $this->wrap = $wrap;
    }

    /**
     * Add attribute data after get
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        return $this->wrap->addGiftExtensionAttribute($order, $this->extensionFactory->create());
    }

    /**
     * Add attribute data after get list
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        return $this->wrap->addGiftExtensionAttributeForItem($searchResult, $this->extensionFactory->create());
    }
}
