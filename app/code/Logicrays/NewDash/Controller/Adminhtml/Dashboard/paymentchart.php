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
 * Get order paymentchart chart data controller
 */
class paymentchart extends \Magento\Framework\App\Action\Action
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

        $collection->getSelect()
                    ->join("sales_order_payment","sales_order_payment.parent_id = main_table.entity_id",
                        ['count(sales_order_payment.method) AS cnt','sales_order_payment.method AS label','sum(main_table.base_grand_total) AS revenu'])
                        ->group('sales_order_payment.method');

        if($this->_request->getParam('filterVal') == '30d' || $this->_request->getParam('isExport') == '30d'){
            $query = $collection->getSelect()
                        ->where("DATE(created_at) >= DATE(NOW()) - INTERVAL 30 DAY");

        }        
        else if($this->_request->getParam('filterVal') == '1m' || $this->_request->getParam('isExport') == '1m'){
            $query = $collection->getSelect()
                        ->where("MONTH(created_at) = MONTH(CURRENT_DATE())")
                        ->where("YEAR(created_at) = YEAR(CURRENT_DATE())");
        }
        else if($this->_request->getParam('filterVal') == '6m' || $this->_request->getParam('isExport') == '6m'){
            $query = $collection->getSelect()
                        ->where('DATE(created_at) >= DATE(NOW()) - INTERVAL 6 MONTH');

        }
        else if($this->_request->getParam('filterVal') == '1y' || $this->_request->getParam('isExport') == '1y'){
            $query = $collection->getSelect()
                        ->where('YEAR(main_table.created_at) = YEAR(CURDATE())');

        }
        else if($this->_request->getParam('filterVal') == 'alltime' || $this->_request->getParam('isExport') == 'alltime'){
            $query = $collection->getSelect();
        }
        else{
            $query = $collection->getSelect()
                        ->where("DATE(created_at) >= DATE(NOW()) - INTERVAL 30 DAY");
        }

        $data = $this->connection->query($query);

        if($this->_request->getParam('isExport')){
            
            $this->directory->create('export');
            $filepath = '/export/paymentgateway.csv';
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();
            $header = ['Order Status', 'Count','Revenu'];
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
