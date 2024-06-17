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

namespace Logicrays\CustomerWallet\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Logicrays\CustomerWallet\Model\CustomerWallet;

class Transaction extends Template
{
    /**
     * CustomerWalletCollection variable
     *
     * @var array
     */
    protected $customerWalletCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * __construct function
     *
     * @param Context $context
     * @param CustomerWallet $customerWalletCollection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        CustomerWallet $customerWalletCollection,
        \Magento\Customer\Model\Session $customerSession,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->customerWalletCollection = $customerWalletCollection;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * _prepareLayout function
     *
     * @return mixed
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Customer Wallet'));
        if ($this->getCustomerWalletCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'custom.history.pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
            ->setShowPerPage(true)->setCollection(
                $this->getCustomerWalletCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCustomerWalletCollection()->load();
        }
        return $this;
    }

    /**
     * Get Pager Html function
     *
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Customer Wallet Collection function
     *
     * @return array
     */
    public function getCustomerWalletCollection()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
        )->getParam('limit') : 5;
        $collection = $this->customerWalletCollection->getCollection()
        ->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('transfer_wallet', 0);
        $collection->setOrder('created_at', 'desc');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Get Currency Symbol function
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->helperData->getCurrentCurrencySymbol();
    }

    /**
     * Get OrderId function
     *
     * @param [type] $orderIncrementId
     * @return string
     */
    public function getOrderId($orderIncrementId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        return $order->getId();
    }
}
