<?php
namespace Logicrays\OrderCancellation\Plugin;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order;

class OrderStatePlugin
{
    private $orderStatusRepository;
    private $orderRepository;
    public function __construct(
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderRepository = $orderRepository;
    }
    public function afterSave(
        Order $subject,
        $result, $object
    ) {
        $status =  $object->getData('status');
        if($status == 'complete') {
            $order = null;
            try {
                $order = $this->orderRepository->get($object->getId());
            } catch (NoSuchEntityException $exception) {
                echo $exception->getMessage();
            }
            $orderHistory = null;
            if ($order) {
                $comment = $order->addStatusHistoryComment(
                    'Order is completed.'
                );
                try {
                    $orderHistory = $this->orderStatusRepository->save($comment);
                } catch (\Exception $exception) {
                    echo $exception->getMessage();
                }
            }
            return $orderHistory;
        }
    }
}