<?php
namespace Logicrays\OrderCancellation\Controller\Index;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;

class CancelOrderProcess extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     *
     * @var Order
     */
    protected $order;

    /**
     *
     * @var Data
     */
    protected $_helper;

    /**
     *
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusRepository;

    /**
     *
     * @var HistoryFactory
     */
    protected $orderHistoryFactory;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param Order $order
     * @param \Logicrays\OrderCancellation\Helper\Data $helper
     * @param OrderStatusHistoryRepositoryInterface $orderStatusRepository
     * @param HistoryFactory $orderHistoryFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        Order $order,
        \Logicrays\OrderCancellation\Helper\Data $helper,
        OrderStatusHistoryRepositoryInterface $orderStatusRepository,
        HistoryFactory $orderHistoryFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->messageManager = $messageManager;
        $this->orderManagement = $orderManagement;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->_helper = $helper;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderHistoryFactory = $orderHistoryFactory;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        return parent::__construct($context);
    }

    /**
     *
     * Saving cancellation reason and sending mail
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $selected_cancel_option = $params['order_cancellation_option'];
        $order_id = $params['order_id'];
        $statusCode = 'request_for_cancellation';
        if ($selected_cancel_option == 'cancel_entire_order') {
            $cancellation_note = $params['order_cancellation_note'];
            $reason = $params['order_cancellation_reason'];
            $label_reason = ucwords(str_replace('_', ' ', $reason));
            $orderRepo = $this->orderRepository->get($order_id);
            $orderHistory = null;
            $commentText = 'A reason for an order cancellation request is ';
            $orderRepo->setOrderCancellationReason($reason);
            $orderRepo->setStatus($statusCode);
            if (!empty($cancellation_note)) {
                $orderRepo->setOrderCancellationNote($cancellation_note);
                $additionalText = 'Additional Note:- '. $cancellation_note;
                $history = $this->orderHistoryFactory->create()
                ->setStatus($statusCode)
                ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                ->setComment(
                    __('Comment:- '. $commentText.$label_reason.'.  '.$additionalText)
                );
                $history->setIsVisibleOnFront(true);
                $orderRepo->addStatusHistory($history);
            } else {
                $history = $this->orderHistoryFactory->create()
                ->setStatus($statusCode)
                ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                ->setComment(
                    __('Comment:- '. $commentText.$label_reason)
                );
                $history->setIsVisibleOnFront(true);
                $orderRepo->addStatusHistory($history);
            }
            $orderRepo->save();
        } elseif ($selected_cancel_option == 'specific_item') {
            $selected_order_cancellation_items = [];
            $order_cancel_items = [];
            $item_cancellation_note = $params['order_cancellation_note'];
            foreach ($params['selected_item'] as $si) {
                $selected_order_cancellation_items[] = $si;
            }
            $order_cancel_items = $selected_order_cancellation_items;
            $orderDetail = $this->order->load($order_id);
            $orderItems = $orderDetail->getAllItems();
            $flag = 0;
            foreach ($orderItems as $value) {
                if (in_array($value['item_id'], $order_cancel_items)) {
                    $order_cancel_items_reason = 'item_cancellation_reason_'.$value['item_id'];
                    $reason = $params[$order_cancel_items_reason];
                    $label_reason = ucwords(str_replace('_', ' ', $reason));
                    $orderHistory = null;
                    $commentText = 'A reason for an order Item '.$value['sku'].' cancellation request is ';
                    $value->setCancelRequest($value->getQtyOrdered());
                    $value->setOrderCancellationReason($reason);
                    if (!empty($item_cancellation_note)) {
                        $value->setOrderCancellationNote($item_cancellation_note);
                        $additionalText = 'Additional Note:-'. $item_cancellation_note;
                        $history = $this->orderHistoryFactory->create()
                        ->setStatus($orderDetail->getStatus())
                        ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                        ->setComment(
                            __('Comment:- '. $commentText.$label_reason.'.  '.$additionalText)
                        );
                        $history->setIsVisibleOnFront(true);
                        $orderDetail->addStatusHistory($history);
                    } else {
                        $history = $this->orderHistoryFactory->create()
                        ->setStatus($orderDetail->getStatus())
                        ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                        ->setComment(
                            __('Comment:- '. $commentText.$label_reason)
                        );
                        $history->setIsVisibleOnFront(true);
                        $orderDetail->addStatusHistory($history);
                    }
                    $value->save();
                }
                if (!empty($value['cancel_request'])) {
                    $flag ++;
                }
                $tot_items = count($orderItems);
                if ($tot_items == $flag) {
                    $orderDetail->setStatus($statusCode);
                }
            }
            $orderDetail->save();
            $types = ['collections','db_ddl','eav'];
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        } else {

        }
        $this->_helper->sendEmail($order_id, $selected_cancel_option);
        $result = $this->resultRedirectFactory->create();
        $this->messageManager->addSuccess(__("Your request for order cancellation has been sent successfully."));
        $result->setPath('sales/order/history');
        return $result;
    }
}
