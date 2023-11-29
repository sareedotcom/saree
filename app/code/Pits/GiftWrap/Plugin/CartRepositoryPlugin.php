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

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;
use Pits\GiftWrap\Model\Wrap;

/**
 * Class CartRepositoryPlugin
 */
class CartRepositoryPlugin
{
    /**
     * Quote Extension Attributes Factory
     *
     * @var QuoteExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var Wrap
     */
    private $wrap;

    /**
     * CartRepositoryPlugin constructor.
     *
     * @param CartExtensionFactory $extensionFactory
     * @param Wrap $wrap
     * @return void
     */
    public function __construct(CartExtensionFactory $extensionFactory, Wrap $wrap)
    {
        $this->extensionFactory = $extensionFactory;
        $this->wrap = $wrap;
    }

    /**
     * Add attribute data after get
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $quote)
    {
        return $this->wrap->addGiftExtensionAttribute($quote, $this->extensionFactory->create());
    }

    /**
     * Add attribute data after get list
     *
     * @param CartRepositoryInterface $subject
     * @param CartSearchResultsInterface $searchResult
     * @return CartSearchResultsInterface
     */
    public function afterGetList(CartRepositoryInterface $subject, CartSearchResultsInterface $searchResult)
    {
        return $this->wrap->addGiftExtensionAttributeForItem($searchResult, $this->extensionFactory->create());
    }
}
