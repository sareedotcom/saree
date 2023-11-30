<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Controller\Adminhtml\AbstractAction;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\Customer as OrderEditorCustomerModel;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;

/**
 * Class Accoun
 */
class Account extends AbstractAction
{
    const ADMIN_RESOURCE = 'MageWorx_OrderEditor::edit_account';

    /**
     * @var OrderEditorCustomerModel
     */
    protected $customer;

    /**
     * Account constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     * @param OrderRepositoryInterface $orderRepository
     * @param MsiStatusManager $msiStatusManager
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param OrderEditorCustomerModel $customer
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        QuoteRepositoryInterface $quoteRepository,
        ShippingModel $shipping,
        PaymentModel $payment,
        OrderRepositoryInterface $orderRepository,
        MsiStatusManager $msiStatusManager,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        OrderEditorCustomerModel $customer
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $helper,
            $scopeConfig,
            $quoteRepository,
            $shipping,
            $payment,
            $orderRepository,
            $msiStatusManager,
            $inventoryDetectionStatusManager
        );
        $this->customer = $customer;
    }

    /**
     * @inheritdoc
     *
     * @return void
     * @throws \Exception
     */
    protected function update()
    {
        $order        = $this->loadOrder();
        $customerId   = $this->getCustomerId();
        $customerData = $this->getCustomerData();

        $this->customer
            ->setOrderId($order->getId())
            ->setCustomerId($customerId)
            ->setCustomerData($customerData)
            ->update();

        // Drop existing order because it recently updated but does not store new info
        $this->clearOrder();
    }

    /**
     * Get customer id from request if specified
     *
     * @return int|null
     */
    protected function getCustomerId()
    {
        $orderData = $this->getRequest()->getParam('order');
        if (!empty($orderData['account']['customer_id'])) {
            return (int)$orderData['account']['customer_id'];
        }

        return null;
    }

    /**
     * Get customer's data from a request if specified
     *
     * @return array
     */
    protected function getCustomerData(): array
    {
        $orderData = $this->getRequest()->getParam('order');
        if (!empty($orderData['account'])) {
            return $orderData['account'];
        }

        return [];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    protected function prepareResponse(): string
    {
        return static::ACTION_RELOAD_PAGE;
    }
}
