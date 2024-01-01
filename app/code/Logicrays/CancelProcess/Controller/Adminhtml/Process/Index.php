<?php

declare(strict_types=1);

namespace Logicrays\CancelProcess\Controller\Adminhtml\Process;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;

class Index extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StockStateInterface
     */
    protected $stockStateInterface;

    /**
     * @param Action\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param StockStateInterface $stockStateInterface
     */
    public function __construct(
        Action\Context $context,
        OrderRepositoryInterface $orderRepository,
        StockStateInterface $stockStateInterface
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->stockStateInterface = $stockStateInterface;
    }

    /**
     * Execute function
     *
     * @return mixed
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
                if ($order->isCanceled()) {
                    $inventoryData = [];
                    foreach ($order->getAllItems() as $item) {
                        foreach ($item->getChildrenItems() as $child) {
                            $product = $child->getProduct();
                            $inventory = $this->stockStateInterface->getStockQty(
                                $product->getId(),
                                $product->getStore()->getWebsiteId()
                            );
                            $inventoryData[] = $inventory;
                            if ($inventory > 0) {
                                $child->setQtyCanceled(0);
                                $child->setTaxCanceled(0);
                                $child->setDiscountTaxCompensationCanceled(0);
                                $item->setQtyCanceled(0);
                                $item->setTaxCanceled(0);
                                $item->setDiscountTaxCompensationCanceled(0);
                            }
                        }
                        if ($item->getProduct()->getTypeID() == 'simple') {
                            $salableQty = $this->stockStateInterface->getStockQty(
                                $item->getProduct()->getId(),
                                $item->getProduct()->getStore()->getWebsiteId()
                            );
                            $salableQtyData[] = $salableQty;
                            if ($salableQty > 0) {
                                $item->setQtyCanceled(0);
                                $item->setTaxCanceled(0);
                                $item->setDiscountTaxCompensationCanceled(0);
                            }
                        }
                    }
                    $checkQty = array_merge($inventoryData, $salableQtyData);
                    if (!in_array(0, $checkQty)) {
                        $order->setSubtotalCanceled(0);
                        $order->setBaseSubtotalCanceled(0);
                        $order->setTaxCanceled(0);
                        $order->setBaseTaxCanceled(0);
                        $order->setShippingCanceled(0);
                        $order->setBaseShippingCanceled(0);
                        $order->setDiscountCanceled(0);
                        $order->setBaseDiscountCanceled(0);
                        $order->setTotalCanceled(0);
                        $order->setBaseTotalCanceled(0);
                        $order->setState(Order::STATE_NEW);
                        $order->setStatus("pending");
                        $this->orderRepository->save($order);
                        $comment = "Order status has change closed to pending";
                        $order->addStatusHistoryComment($comment)
                            ->setIsCustomerNotified(false)  // Set to true if you want to notify the customer
                            ->save();
                        $this->messageManager->addSuccessMessage(__('Order status has been changed.'));
                    } else {
                        $this->messageManager->addErrorMessage(__('Product Is Out Of Stock'));
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error occurred while changing order status.'));
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
