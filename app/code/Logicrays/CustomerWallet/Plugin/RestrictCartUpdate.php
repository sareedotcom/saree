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

namespace Logicrays\CustomerWallet\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart as CheckoutCart;

class RestrictCartUpdate
{
    /**
     * CheckoutSession variable
     *
     * @var checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param Session $checkoutSession
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     */
    public function __construct(
        Session $checkoutSession,
        \Logicrays\CustomerWallet\Helper\Data $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
    }

    /**
     * Before Update Cart Items function
     *
     * @param CheckoutCart $subject
     * @param array $data
     * @return mixed
     */
    public function beforeUpdateItems(
        CheckoutCart $subject,
        $data
    ) {
        $checkoutData = $this->checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($checkoutData as $checkoutItems) {
            if ($checkoutItems->getSku() == $this->helperData->walletSKU()) {
                $itemId = $checkoutItems->getItemId();
                $quote = $this->checkoutSession->getQuote();
                // set the item ID of the product you want to exclude from cart updates
                $item = $quote->getItemById($itemId);
                if ($item) {
                    $data = [];
                }
                return [$data];
            }
        }
    }
}
