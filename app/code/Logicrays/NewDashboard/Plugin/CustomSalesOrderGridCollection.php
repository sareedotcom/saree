<?php 

    namespace Logicrays\NewDashboard\Plugin;

    use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
    use Magento\Framework\App\Request\Http;

    class CustomSalesOrderGridCollection
    {
        private $collection;

        public function __construct(
            SalesOrderGridCollection $collection,
            Http $request
        ) {
            $this->collection = $collection;
            $this->request = $request;
        }

        public function aroundGetReport(
            \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
            \Closure $proceed,
            $requestName
        ) {
            $result = $proceed($requestName);

            $filters = $this->request->getParams('filters');
            $sorting = $this->request->getParams('sorting');
            
            if(isset($filters['filters'])){

                if($filters['namespace'] == 'lrdes_order_listing'){
                    $applyedFilters = $filters['filters'];
                    if(count($applyedFilters) > 1 || $filters['search']){
                        // Nothing to do anything if other filter is applyed
                    }
                    else{
                        if ($result instanceof $this->collection) {
                            // $this->collection->addFieldToFilter('main_table.state', array('in' => array('processing')));
                            $this->collection->getSelect()->joinLeft(
                                ['so' => 'sales_order'],
                                'main_table.increment_id = so.increment_id',
                                ['state']
                            )->where("so.status IN('processing','cod_prepaid','cod_processing','under_procurement','alternate_option','pre_qc','exchange','under_smoothing','post_qc','dispatched','partial_dispatched')");
                            
                            if(!isset($sorting["sorting"])){
                                $this->collection->getSelect()->order('created_at DESC');
                            }
                            return $this->collection;
                        }
                    }
                }
            }
            return $result;
        }
    }