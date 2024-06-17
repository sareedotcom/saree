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

namespace Logicrays\CustomerWallet\ViewModel;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class WalletRequest extends DataObject implements ArgumentInterface
{
    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * __construct function
     *
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get Wallet Reorder function
     *
     * @param int $orderId
     * @return string
     */
    public function getWalletReorder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $items = $order->getAllItems();
        // return $items;
        $walletSKU = $this->helperData->walletSKU();
        foreach ($items as $item) {
            if ($item->getSku() == $walletSKU) {
                return 1;
            }
            return 0;
        }
    }

    /**
     * Get Wallet Sku function
     *
     * @return string
     */
    public function getWalletSku()
    {
        return $this->helperData->walletSKU();
    }
}
