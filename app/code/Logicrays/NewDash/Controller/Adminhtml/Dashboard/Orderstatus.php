<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Logicrays\NewDash\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Dashboard;
use Magento\Backend\Model\Dashboard\Chart;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Get order Orderstatus chart data controller
 */
class Orderstatus extends \Magento\Framework\App\Action\Action
{
    protected $uploaderFactory;

    protected $_locationFactory; 

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
        )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_request = $request;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->_fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        
        return parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToSelect('status')->getSelect()
                    ->join("sales_order_status","sales_order_status.status = main_table.status",
                        ['count(main_table.status) AS cnt','sales_order_status.label AS label','sum(main_table.base_grand_total) AS revenu'])
                    ->where("sales_order_status.status != ''")
                    ->where("sales_order_status.status != 'order_cancel'")
                    ->where("sales_order_status.status != 'procurement_under_process'")
                    ->where("sales_order_status.status != 'customer_response'")
                    ->where("sales_order_status.status != 'pending_payment'")
                    ->where("sales_order_status.status != 'pending_paypal'")
                    ->where("sales_order_status.status != 'payment_review'");


        if($this->_request->getParam('filterVal') == '30d' || $this->_request->getParam('isExport') == '30d'){
            $query = $collection->getSelect()
                        ->where("DATE(created_at) >= DATE(NOW()) - INTERVAL 30 DAY")
                        ->group('main_table.status');

        }        
        else if($this->_request->getParam('filterVal') == '1m' || $this->_request->getParam('isExport') == '1m'){
            $query = $collection->getSelect()
                        ->where("MONTH(created_at) = MONTH(CURRENT_DATE())")
                        ->where("YEAR(created_at) = YEAR(CURRENT_DATE())")
                        ->group('main_table.status');
        }
        else if($this->_request->getParam('filterVal') == '6m' || $this->_request->getParam('isExport') == '6m'){
            $query = $collection->getSelect()
                        ->where('DATE(created_at) >= DATE(NOW()) - INTERVAL 6 MONTH')
                        ->group('main_table.status');

        }
        else if($this->_request->getParam('filterVal') == '1y' || $this->_request->getParam('isExport') == '1y'){
            $query = $collection->getSelect()
                        ->where('YEAR(main_table.created_at) = YEAR(CURDATE())')
                        ->group('main_table.status');

        }
        else if($this->_request->getParam('filterVal') == 'alltime' || $this->_request->getParam('isExport') == 'alltime'){
            $query = $collection->getSelect()
                        ->group('main_table.status');
        }
        else{
            $query = $collection->getSelect()
                        ->where("DATE(created_at) >= DATE(NOW()) - INTERVAL 30 DAY")
                        ->group('main_table.status');       
        }

        $data = $this->connection->query($query);

        if($this->_request->getParam('isExport')){
            
            $this->directory->create('export');
            $filepath = '/export/orderstatus.csv';
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();
            $header = ['Payment Gateway', 'Count','Revenu'];
            $stream->writeCsv($header);
            foreach ($data as $chartData) {
                $data1 = [];
                $data1[] = $chartData['label'];
                $data1[] = $chartData['cnt'];
                $data1[] = $chartData['revenu'];
                $stream->writeCsv($data1);
            }
            $result = $this->resultJsonFactory->create();
            $result->setData(DirectoryList::MEDIA.$filepath);
            return $result;
        }
        
        $count = [];
        $label = [];
        $revenu = 0;
        foreach ($data as $key => $value) {
            $label[] = $value['label'];
            $count[] = $value['cnt'];
        }

        $data = [$label, $count];
        $result = $this->resultJsonFactory->create();
        $result->setData($data);
        return $result;
        exit;
    }

    /**
     * Check Grid List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
            return $this->_authorization->isAllowed('Logicrays_NewDashboard::ordergrid');
    }
}
