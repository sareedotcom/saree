<?php
namespace Logicrays\UpdateOrder\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Serialize\SerializerInterface;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ResourceConnection $resource
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param JsonFactory $jsonResultFactory
     * @param ManagerInterface $messageManager
     * @param QuoteRepository $quoteRepository
     * @param SerializerInterface $serializerInterface
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ResourceConnection $resource,
        OrderItemRepositoryInterface $orderItemRepository,
        JsonFactory $jsonResultFactory,
        ManagerInterface $messageManager,
        QuoteRepository $quoteRepository,
        SerializerInterface $serializerInterface,
        Data $helper
    ) {
        parent::__construct(
            $context
        );
        $this->resultPageFactory = $resultPageFactory;
        $this->_resource = $resource;
        $this->orderItemRepository = $orderItemRepository;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->messageManager = $messageManager;
        $this->quoteRepository = $quoteRepository;
        $this->serializerInterface = $serializerInterface;
        $this->helper = $helper;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $measureedOptions = $this->getRequest()->getParam('measureedoptions');
        $stichingOptionSelected = $this->getRequest()->getParam('stiching_selected');
        $itemId = $this->getRequest()->getParam('itemId');
        $orderId = $this->getRequest()->getParam('orderId');
        
        if($measureedOptions){
            $finalOption = [];
            $optionArr = json_decode($measureedOptions);

            foreach($optionArr AS $value){
                $optionData = explode("_",$value);
                $finalOption[$optionData[2]][] = $optionData;
            }
            $option = [];

            $optionList = [];
            $optionList1 = [];
            $days = 0;
            foreach($finalOption AS $val){
                if($val[0][3] == "radio"){
                    $option[$val[0][2]] = $val[0][4];
                    $optionList['label'] = $val[0][0];
                    $optionList['value'] = $val[0][1];
                    $optionList['print_value'] = $val[0][1];
                    $optionList['option_id'] = $val[0][2];
                    $optionList['option_type'] = 'radio';
                    $optionList['option_value'] = $val[0][4];
                    $optionList['custom_view'] = false;
                }
                else if($val[0][3] == "checkbox"){
                    $option[$val[0][2]] = $val[0][4];

                    $optionList['label'] = $val[0][0];
                    $optionList['value'] = $val[0][1];
                    $optionList['print_value'] = $val[0][1];
                    $optionList['option_id'] = $val[0][2];
                    $optionList['option_type'] = 'checkbox';
                    $optionList['option_value'] = $val[0][4];
                    $optionList['custom_view'] = false;
                }
                else if($val[0][3] == "drop" && $val[0][4] == "down"){

                    if(strripos($val[0][1],'Days') > -1 && $days == 0){
                        $str = strtolower($val[0][1]);
                        $arr = explode(" ",$str);
                        $toIndex = array_search("to",$arr);
                        $days = $arr[$toIndex+1];
                    }

                    $option[$val[0][2]] = $val[0][5];

                    $optionList['label'] = $val[0][0];
                    $optionList['value'] = $val[0][1];
                    $optionList['print_value'] = $val[0][1];
                    $optionList['option_id'] = $val[0][2];
                    $optionList['option_type'] = 'drop_down';
                    $optionList['option_value'] = $val[0][5];
                    $optionList['custom_view'] = false;
                }
                $optionList1[] = $optionList;
            }
            $itemCollection = $this->orderItemRepository->get($itemId);
            $currentProduct = $itemCollection->getProduct();

            $options = $itemCollection->getProductOptions();
            $optionsnew = $options['info_buyRequest']['options'];
            foreach($options AS $key => $optionVal){
                if("options" == $key){
                    $options[$key] = $optionList1;
                }
                else if("info_buyRequest" == $key){
                    $options[$key]['options'] = $option;
                }
            }
            foreach ($optionsnew as $key => $value) {
                if(!isset($options["info_buyRequest"]["options"][$key])){
                    $options["info_buyRequest"]["options"][$key] = "";
                }
            }
            ksort($options["info_buyRequest"]["options"]);
            $itemCollection->setProductOptions($options);
            $itemCollection->save();
            $order = $itemCollection->getOrder();
            $order->addStatusHistoryComment('sku:'.$itemCollection->getSku().' stitching option is added by customer');
            $order->save();
            
            $connection = $this->_resource->getConnection();
            $quote = $this->quoteRepository->get($order->getQuoteId());

            foreach($quote->getAllVisibleItems() as $itemq){
                
                $sql = $connection->select()->from(
                    ['sales_order_item' => "sales_order_item"],
                    ['quote_item_id'])
                ->where(
                    "sales_order_item.item_id = ?", $itemId);

                $quoteItemId = $connection->fetchOne($sql);
                if($quoteItemId == $itemq->getId()){
                    $quoteOptions = $itemq->getProduct()->getTypeInstance(true)->getOrderOptions($itemq->getProduct());
                    $quoteOptions['info_buyRequest']['options'] = $options["info_buyRequest"]["options"];
                }
            }

            $sql = $connection->select()
                ->from(
                    ['quote_item_option' => "quote_item_option"],
                    ['value','option_id'])
                ->where(
                    "quote_item_option.code = ?", "info_buyRequest")
                ->where(
                    "quote_item_option.item_id = ?", $itemCollection->getQuoteItemId())
                ->order("quote_item_option.option_id DESC");
            
            $result = $connection->fetchRow($sql);
            $quoteItemOptionData = $this->serializerInterface->unserialize($result['value']);
            $quoteItemOptionData['options'] = $quoteOptions['info_buyRequest']['options'];
            $quoteItemOptionData = $this->serializerInterface->serialize($quoteItemOptionData);

            $data = ["value"=>$quoteItemOptionData];

            $where = ['quote_item_option.option_id = ?' => $result["option_id"]];

            $tableName = $connection->getTableName("quote_item_option");

            $updatedRows=$connection->update($tableName, $data, $where);

            $changeToProcessing = 0;
            foreach ($order->getItemsCollection() as $item) {
                $itemOptions = $item->getProductOptions();
                if(isset($itemOptions['options'])){
                    foreach ($itemOptions['options'] as $value) {
                        if($value['print_value'] == "Later"){
                            $changeToProcessing = 1;
                            break;
                        }
                    }
                }
            }

            if ($days) {
                $updCateEstimationDate = $this->helper->getOptionDeliveryDay($currentProduct, $days);
            } else {
                $extraWorkingDays = 0;
                $updCateEstimationDate = $this->helper->getDeliveryEstimationDate($currentProduct, $extraWorkingDays);
            }
            
            $estData = ["estd_dispatch_date"=>$updCateEstimationDate];
            $estWhere = ['item_id = ?' => $itemCollection->getQuoteItemId()];
            $qiTableName = $connection->getTableName("quote_item");
            $updatedRows = $connection->update($qiTableName, $estData, $estWhere);

            $soiestWhere = ['quote_item_id = ?' => $itemCollection->getQuoteItemId()];
            $soiTableName = $connection->getTableName("sales_order_item");
            $updatedRows = $connection->update($soiTableName, $estData, $soiestWhere);


            if(!$changeToProcessing){
                $order->setState("processing")->setStatus("processing");
                $order->save();
            }
            $this->messageManager->addSuccess(__("Measurement submitted successfully"));
            exit;
        }
        else if($stichingOptionSelected){

            $connection = $this->_resource->getConnection();
            $select = $connection->select()
                ->from(
                    ['catalog_product_option' => "catalog_product_option"],
                    ['*'])
                ->where(
                    "catalog_product_option.product_id = ?", $stichingOptionSelected)
                ->order("catalog_product_option.sort_order ASC");

            $data = $connection->fetchAll($select);
            $resultData = [];
            foreach($data AS $val){
                $select = $connection->select()
                            ->from(
                                ['catalog_product_option_type_value' => "catalog_product_option_type_value"],
                                ['*'])
                            ->join(
                                ['catalog_product_option'=>'catalog_product_option'],
                                "catalog_product_option.option_id = catalog_product_option_type_value.option_id")
                            ->join(
                                ['catalog_product_option_type_title'=>'catalog_product_option_type_title'],
                                "catalog_product_option_type_title.option_type_id = catalog_product_option_type_value.option_type_id",
                                [
                                    '*',
                                    'optiontypetitle' => 'catalog_product_option_type_title.title',
                                ])
                            ->join(
                                ['optiondependent_value'=>'optiondependent_value'],
                                "optiondependent_value.option_type_id = catalog_product_option_type_title.option_type_id")
                            ->join(
                                ['catalog_product_option_type_price'=>'catalog_product_option_type_price'],
                                "catalog_product_option_type_price.option_type_id = catalog_product_option_type_title.option_type_id")
                            ->join(
                                ['catalog_product_option_title'=>'catalog_product_option_title'],
                                "catalog_product_option_title.option_id = catalog_product_option_type_value.option_id",
                                [
                                    '*',
                                    'optiontitle' => 'catalog_product_option_title.title'
                                ])
                            ->where(
                                "catalog_product_option_type_value.option_id = ?", $val['option_id']
                            );
                $resultData[] = $connection->fetchAll($select);
            }
            $result = $this->jsonResultFactory->create();
            return $result->setData(['result' => $resultData, "itemId" => $itemId]);
        }
        exit;

    }

}