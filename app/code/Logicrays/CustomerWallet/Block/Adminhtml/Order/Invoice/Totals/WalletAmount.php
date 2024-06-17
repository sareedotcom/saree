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

namespace Logicrays\CustomerWallet\Block\Adminhtml\Order\Invoice\Totals;

class WalletAmount extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    private $helperData;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Invoice|null
     */
    protected $_invoice = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get Invoice
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }

    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $totalWalletAmount = (float)$this->helperData
                            ->getTotalWalletAmountFromOrder($this->getParentBlock()->getOrder());

        if (abs($totalWalletAmount)) {
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout(),
                    'label' => __('Wallet Amount Applied'),
                    'value' => $totalWalletAmount,
                    'base_value' => $totalWalletAmount
                ]
            );
            $this->getParentBlock()->addTotal($total);
        }

        return $this;
    }
}
