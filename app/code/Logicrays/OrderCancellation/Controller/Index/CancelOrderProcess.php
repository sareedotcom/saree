<?php
namespace Logicrays\OrderCancellation\Controller\Index;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class CancelOrderProcess extends \Magento\Framework\App\Action\Action
{
    protected $orderManagement;
    protected $orderRepository;
    protected $order;
    protected $_helper;
    private $orderStatusRepository;
    protected $orderHistoryFactory;

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
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
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
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->_storeManager = $storeManager; 
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        return parent::__construct($context);
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $selected_cancel_option = $params['order_cancellation_option'];
        $order_id = $params['order_id'];
        $statusCode = 'request_for_cancellation';
        $CancelRequestItemImages = 'CancelRequestItemImages/'; // Storage folder

        $cancelItemReqImage1 = 'upload_cancelreq_file1';
        $cancelItemReqImage2 = 'upload_cancelreq_file2';
        $attachedImages = [];
        
        
        $file1 = $this->getRequest()->getFiles($cancelItemReqImage1);
        if($file1['name']){
            $ext = pathinfo($file1['name'], PATHINFO_EXTENSION);
            $fileName = ($file1 && array_key_exists('name', $file1)) ? time().$params['order_id']."_File1.".$ext : null;
            $target = $this->mediaDirectory->getAbsolutePath($CancelRequestItemImages); 
            $uploader = $this->fileUploader->create(['fileId' => $cancelItemReqImage1]);
            $uploader->setAllowedExtensions(['jpg', 'png']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true); 
            // $uploader->save($target);
            $uploader->save($target, time().$params['order_id']."_File1.".$ext);
            $currentStore = $this->_storeManager->getStore();
            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $img1 = $mediaUrl.'CancelRequestItemImages/'.$fileName;
            $attachedImages['img1'] = $img1;
        }

        $file2 = $this->getRequest()->getFiles($cancelItemReqImage2);
        if($file2['name']){
            $ext = pathinfo($file2['name'], PATHINFO_EXTENSION);
            $fileName2 = ($file2 && array_key_exists('name', $file2)) ? time().$params['order_id']."_File2.".$ext : null;
            $target = $this->mediaDirectory->getAbsolutePath($CancelRequestItemImages); 
            $uploader = $this->fileUploader->create(['fileId' => $cancelItemReqImage2]);
            $uploader->setAllowedExtensions(['jpg', 'png']);
            $uploader->setAllowCreateFolders(true);
            $uploader->setAllowRenameFiles(true); 
            // $uploader->save($target);
            $uploader->save($target, time().$params['order_id']."_File2.".$ext);
            $currentStore = $this->_storeManager->getStore();
            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $img2 = $mediaUrl.'CancelRequestItemImages/'.$fileName2;
            $attachedImages['img2'] = $img2;
        }

        if($selected_cancel_option == 'cancel_entire_order'){
            $cancellation_note = $params['order_cancellation_note'];
            $reason = $params['order_cancellation_reason'];
            $label_reason = ucwords(str_replace('_', ' ',$reason));
            $orderRepo = $this->orderRepository->get($order_id);
            $orderHistory = null;
            $commentText = 'A reason for an order cancellation request is ';
            $orderRepo->setOrderCancellationReason($reason);
            $orderRepo->setStatus($statusCode);
            if(!empty($cancellation_note)){
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
            }
            else {
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
        }
        else if($selected_cancel_option == 'specific_item') {
            $selected_order_cancellation_items = array();
            $order_cancel_items = array();
            $item_cancellation_note = $params['order_cancellation_note'];
            foreach($params['selected_item'] as $si){
                $selected_order_cancellation_items[] = $si;
            }
            $order_cancel_items = $selected_order_cancellation_items;
            $orderDetail = $this->order->load($order_id);
            $orderItems = $orderDetail->getAllItems();
            $flag = 0;
            foreach ($orderItems as $value) {
                if(in_array($value['item_id'],$order_cancel_items))
                {
                    $order_cancel_items_reason = 'item_cancellation_reason_'.$value['item_id'];
                    $qtyToCancel = 'item_cancellation_qty_'.$value['item_id'];
                    $reason = $params[$order_cancel_items_reason];
                    $qtyToCancel = $params[$qtyToCancel];
                    $label_reason = ucwords(str_replace('_', ' ',$reason));
                    $orderHistory = null;
                    $commentText = 'A reason for an order Item '.$value['sku'].' cancellation request Qty with '.$qtyToCancel.' is ';
                    // echo $commentText;
                    $value->setCancelRequest($qtyToCancel);
                    $value->setLrItemStatus('request_for_cancellation');
                    $value->setOrderCancellationReason($reason);
                    // print_r($params);die;
                    if(!empty($item_cancellation_note)){
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
                    }
                    else {
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
                if($value['cancel_request'] >= '1.0000'){
                    $flag ++;
                }
                $tot_items = count($orderItems);
                if($tot_items == $flag){
                    $orderDetail->setStatus($statusCode);
                }
            }
            $orderDetail->save();
            $types = array('collections','db_ddl','eav');
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }
        else {

        }

        $this->_helper->sendEmail($order_id,$selected_cancel_option,$attachedImages);
        $result = $this->resultRedirectFactory->create();
        $this->messageManager->addSuccess(__("Your cancellation request has been successfully submitted. Please allow 48 hours for it to be processed and approved."));
        $result->setPath('sales/order/history');
        return $result;
    }
}