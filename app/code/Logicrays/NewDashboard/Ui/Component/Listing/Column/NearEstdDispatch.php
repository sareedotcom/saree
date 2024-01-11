<?php
namespace Logicrays\NewDashboard\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\App\ResourceConnection;

class NearEstdDispatch extends Column
{
	protected $_orderRepository;
    protected $_resource;

	public function __construct(
    	ContextInterface $context,
    	UiComponentFactory $uiComponentFactory,
    	OrderRepositoryInterface $orderRepository,
        ResourceConnection $resource,
    	array $components = [],
    	array $data = [])
	{
    	$this->_orderRepository = $orderRepository;
        $this->_resource = $resource;

    	parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	public function prepareDataSource(array $dataSource)
	{
        $connection = $this->_resource->getConnection();

      	if (isset($dataSource['data']['items'])) {

        	foreach ($dataSource['data']['items'] as & $item) {
            	$order  = $this->_orderRepository->get($item["entity_id"]);

                $allItems = $order->getItemsCollection();
                $dateArr = [];
                $isCancelRequest = 0;
                if($order->getStatus() == 'request_for_cancellation'){
                    $isCancelRequest = 1;
                }
                foreach($allItems AS $data){
                    $select = $connection->select()
                        ->from(
                            ['main' => 'sales_order_item'],
                            ['estd_dispatch_date','cancel_request']
                        )
                        ->where(
                            "main.quote_item_id = :quote_item_id"
                        )
                        ->where(
                            "main.estd_dispatch_date != ''"
                        )
                        ->where(
                            "main.product_type != :productType"
                        );
                    $bind = ['quote_item_id'=> $data->getQuoteItemId(), 'productType' => 'configurable'];
                    $result = $connection->fetchRow($select, $bind);
                    if(isset($result['estd_dispatch_date'])){
                        $dateForInd = date_format(date_create($result['estd_dispatch_date']),"Y/m/d");
                        $dateArr[] =  $dateForInd;
                    }
                    $dateForDisplay = "";
                    if(count($dateArr)){
                        $minDate = min($dateArr);
                        if($minDate){
                            $dateForDisplay = date_format(date_create($minDate),"l, d F Y");
                        }
                    }
                    if(isset($result['cancel_request'])){
                        $isCancelRequest = 1;
                    }

                   if($dateForDisplay && count($dateArr) > 1){
                        $nearestDate=date_create($minDate);
                        $todayDate=date_create(date("Y-m-d"));
                        $diff=date_diff($todayDate,$nearestDate);
                        if($diff->format("%R%a") <= 1 && $diff->format("%R%a") >= 0) {
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='red-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                        }
                        else if($diff->format("%R%a") <= 2 && $diff->format("%R%a") > 0){
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='lightpink-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                        }
                        else{
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='white-estimate'>".$dateForDisplay."<br>&#x2705;<span>";
                            }
                        }
                    }
                    else if($dateForDisplay)
                    {
                        $nearestDate=date_create($minDate);
                        $todayDate=date_create(date("Y-m-d"));
                        $diff=date_diff($todayDate,$nearestDate);
                        if($diff->format("%R%a") <= 1 && $diff->format("%R%a") >= 0){
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='red-estimate'>".$dateForDisplay."<span>";
                            }
                        }
                        else if($diff->format("%R%a") <= 2 && $diff->format("%R%a") > 0){
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='lightpink-estimate'>".$dateForDisplay."<span>";
                            }
                        }
                        else{
                            if($isCancelRequest){
                                $item[$this->getData('name')] = "<span class='yellow-estimate'>".$dateForDisplay."<span>";
                            }
                            else{
                                $item[$this->getData('name')] = "<span class='white-estimate'>".$dateForDisplay."<span>";
                            }
                        }
                    }
                    else{
                        $item[$this->getData('name')] = '';
                    }
                }
                
        	}
    	}
    	return $dataSource;
	}
}