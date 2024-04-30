<?php declare(strict_types=1);

namespace DEG\CustomReports\Controller\Adminhtml\CustomReport;

use DEG\CustomReports\Model\CustomReport;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\Framework\App\ResourceConnection;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'DEG_CustomReports::customreport_save';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Action\Context                                         $context
     * @param DataPersistorInterface                                 $dataPersistor
     * @param CollectionFactory                                      $orderCollectionFactory
     * @param Product                                                $product
     * @param Filesystem                                             $filesystem
     * @param FileFactory                                            $fileFactory
     * @param CategoryFactory                                        $categoryFactory
     * @param CountryFactory                                         $countryFactory
     * @param ScopeConfigInterface                                   $scopeConfig
     * @param Collection                                             $creditmemoCollection
     * @param ProductCategoryList                                    $categoryList = null
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $orderCollectionFactory,
        Product $product,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CategoryFactory $categoryFactory,
        CountryFactory $countryFactory,
        ScopeConfigInterface $scopeConfig,
        Collection $creditmemoCollection,
        ResourceConnection $resourceConnection,
        ProductCategoryList $categoryList = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->product = $product;
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->creditmemoCollection = $creditmemoCollection;
        $this->resourceConnection = $resourceConnection;
        $this->productCategoryList = $categoryList ?: ObjectManager::getInstance()->get(ProductCategoryList::class);
        parent::__construct($context);

    }

    /**
     * Save action
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $main = [];
        $data = $this->getRequest()->getPostValue();
        $startDate = date("Y-m-d",strtotime($data['start_date']));
        $endDate = date("Y-m-d",strtotime($data['to_date']));
        
        if($data['report_type'] == "refunded"){
            $creditmemoCollection = $this->creditmemoCollection;
            $creditmemoCollection->addFieldToFilter('created_at', array('gteq' => $startDate));
            $creditmemoCollection->addFieldToFilter('created_at', array('lteq' => $endDate));
        }
        else{
            $collection = $this->_orderCollectionFactory->create();
            $collection->addFieldToFilter('status', ['neq' => 'canceled'])
                ->addFieldToFilter('status', ['neq' => 'closed'])
                ->addFieldToFilter('state', ['neq' => 'canceled'])
                ->addFieldToFilter('state', ['neq' => 'closed']);
            $collection->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
            // $collection->addAttributeToFilter('customer_email', 'harsh@logicrays.com');
            // $collection->setPageSize(1000)->setCurPage(8);  // first page (means limit 0,10)
            $collection->load();
        }

        if($data['report_type'] == "shipping_country_wise_category" || $data['report_type'] == "billing_country_wise_category"){
            $configCat = $this->scopeConfig->getValue(
                'report/general/category_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );
            $categoryIds = explode(',', $configCat);
            
            $catNameArr = [];
            foreach ($categoryIds as $key => $value) {
                $category = $this->_categoryFactory->create()->load($value);
                $catNameArr[$value] = $category->getName();
            }
            foreach ($collection->getItems() as $key => $value) {

                if($data['report_type'] == "shipping_country_wise_category"){
                    $address = $value->getShippingAddress();
                }
                else if($data['report_type'] == "billing_country_wise_category"){
                    $address = $value->getBillingAddress();
                }
                
                if(isset($address)){
                    $countryId = $address->getCountryId();
                    $country = $this->countryFactory->create()->loadByCode($countryId);
                    $countryId = $country->getName();
                    $arr = [];
                    foreach ($value->getAllVisibleItems() as $value1) {

                        $productId = $this->product->getIdBySku($value1->getSku());
                        $product = $this->product->load($productId);

                        $productCategory = $this->productCategoryList->getCategoryIds($productId);
                        $productCategory = array_unique($productCategory);
                        $pid = $product->getId();
                        if($pid){
                            if($productCategory){
                                if($product->getQtyOrdered() > 0 || $value1->getQtyToRefund()){
                                    $catArr = [];
                                    $catArr = array_intersect($categoryIds, $productCategory);
                                    if(!empty($catArr)){
                                        foreach ($catArr as $pCat) {
                                            $arr[$value->getId()][] = $product->getSku();
                                            if(isset($main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'])){
                                                if($value->hasInvoices() || $value->hasShipments()){
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] = $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] + $value1->getQtyToRefund();
                                                }
                                                else{
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] = $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] + $value1->getQtyOrdered();
                                                }
                                                $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'] = $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'] + ($value1->getBaseRowTotal() - $value1->getBaseDiscountAmount());
                                                $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['avg_of_product_amount'] =  $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'] / $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'];
                                                if(!in_array($product->getSku(), $arr)){
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['countunique_of_order_number'] = $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['countunique_of_order_number'] + 1;
                                                }
                                            }
                                            else{
                                                if(($value->hasInvoices() || $value->hasShipments()) && $value1->getQtyToRefund()){
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] = $value1->getQtyToRefund();
                                                }
                                                else{
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'] = $value1->getQtyOrdered();
                                                }
                                                $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'] = $value1->getBaseRowTotal() - $value1->getBaseDiscountAmount();
                                                $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['avg_of_product_amount'] =  $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'] / $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product'];
                                                if(!in_array($product->getSku(), $arr)){
                                                    $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['countunique_of_order_number'] = 1;
                                                }
                                            }
                                            $main[$countryId][$value->getIncrementId()][$pCat]['sum_of_product_amount_for_avg'][] = $main[$countryId][$value->getIncrementId()][$pCat][$product->getSku()]['sum_of_product_amount'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $sumOfAllProduct = 0;
            foreach ($main as $key => $value) {
                foreach ($main[$key] as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        $sumOfAllProduct = $sumOfAllProduct + array_sum($value2['sum_of_product_amount_for_avg']);
                        unset($main[$key][$key1][$key2]['sum_of_product_amount_for_avg']);
                    }
                }
            }
            foreach ($main as $key => $value) {
                foreach ($main[$key] as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            $main[$key][$key1][$key2][$key3]['sum_of_product_amount_avg'] = $value3['sum_of_product_amount']/$sumOfAllProduct*100;
                        }
                    }
                }
            }
            if($data['report_type'] == "shipping_country_wise_category"){
                $fieldTitle = 'Shipping Country';
            }
            else if($data['report_type'] == "billing_country_wise_category"){
                $fieldTitle = 'Billing Country';
            }
            $fileName = $fieldTitle." - ".$data['start_date']." TO ".$data['to_date'].'.csv';
            $filepath = 'export/'.$fileName;
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create('export');
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();

            $header = [$fieldTitle, 'Category', 'OrderId', 'SKU','SUM of No of Products','SUM of Product Amount','AVERAGE of Product Amount','SUM of Product Amount','COUNTUNIQUE of Order Number'];
            $stream->writeCsv($header);

            $total = [];
            $total[] = "Grand Total";
            $total[] = "";
            $total[] = "";
            $total[] = "";
            $total['sum_of_product'] = 0;
            $total['sum_of_product_amount'] = 0;
            $total['avg_of_product_amount'] = 0;
            $total['sum_of_product_amount_avg'] = 0;
            $total['countunique_of_order_number'] = 0;
            foreach ($main as $key => $value) {
                foreach ($main[$key] as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            $csvData = [];
                            $csvData[] = $key;
                            $csvData[] = $catNameArr[$key2];
                            $csvData[] = $key1;
                            $csvData[] = $key3;
                            $csvData[] = $value3['sum_of_product'];
                            $csvData[] = number_format($value3['sum_of_product_amount'],2);
                            $csvData[] = number_format($value3['avg_of_product_amount'],2);
                            $csvData[] = number_format($value3['sum_of_product_amount_avg'],2);
                            $csvData[] = number_format($value3['countunique_of_order_number'],2);
                            $total['sum_of_product'] =  $total['sum_of_product'] + $value3['sum_of_product'];
                            $total['sum_of_product_amount'] =  $total['sum_of_product_amount'] + $value3['sum_of_product_amount'];
                            $total['avg_of_product_amount'] =  $total['avg_of_product_amount'] + $value3['avg_of_product_amount'];
                            $total['sum_of_product_amount_avg'] =  $total['sum_of_product_amount_avg'] + $value3['sum_of_product_amount_avg'];
                            $total['countunique_of_order_number'] =  $total['countunique_of_order_number'] + $value3['countunique_of_order_number'];
                            $stream->writeCsv($csvData);
                        }
                    }
                }
            }
            
            $stream->writeCsv($total);

            $downloadedFileName = $fileName;
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
            
            return $resultRedirect->setPath('*/*/listing');
        }
        else if($data['report_type'] == "payment_gateway"){
            $main = [];
            foreach ($collection->getItems() as $key => $value) {

                $payment = $value->getPayment();
                $method = $payment->getMethodInstance();
                $methodTitle = $method->getTitle();
                $address = $value->getShippingAddress();
                $countryId = "";
                if($address){
                    $countryId = $address->getCountryId();
                }
                foreach ($value->getAllVisibleItems() as $value1) {
                    if($countryId){
                        $country = $this->countryFactory->create()->loadByCode($countryId);
                        $countryName = $country->getName();
                        if("Send an invoice to the customer by email (via Stripe Billing)" == $methodTitle){
                            $methodTitle = "Stripe Billing";
                        }
                        if(isset($main[$methodTitle][$countryName][$value->getIncrementId()][$value->getIncrementId()][$value1->getSku()]['count_of_order_number'])){
                            $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['count_of_order_number'] = $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['count_of_order_number'] + 1;
                            $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] +  $value1->getBaseRowTotal();
                        }
                        else{
                            $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['count_of_order_number'] = 1;
                            $main[$methodTitle][$countryName][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $value1->getBaseRowTotal();
                        }
                    }
                }                
            }

            $fileName = "PaymentGateway-".$data['start_date']." TO ".$data['to_date'].'.csv';
            $filepath = 'export/PaymentGateway - '.$fileName;
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

            $directory->create('export');
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();

            $header = ["Payment Gateway", 'Shipping Country', 'OrderId', 'SKU', 'COUNTA of Order Number','SUM of Product Amount'];
            $stream->writeCsv($header);
            $total = [];
            foreach ($main as $key => $value) {
                foreach ($main[$key] as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {

                            $csvData = [];
                            $csvData[] = $key;
                            $csvData[] = $key1;
                            $csvData[] = $key2;
                            $csvData[] = $key3;
                            $csvData[] = $value3['count_of_order_number'];
                            $csvData[] = $value3['sum_of_product_amount'];
                            $rowTotal['count_of_order_number'][] = $value3['count_of_order_number'];
                            $rowTotal['sum_of_product_amount'][] = $value3['sum_of_product_amount'];
                            $stream->writeCsv($csvData);
                        }
                    }
                }
            }
            if(isset($value3)){
                $total[] = "Grand Total";
                $total[] = "";
                $total[] = "";
                $total[] = "";
                $total[] = array_sum($rowTotal['count_of_order_number']);
                $total[] = array_sum($rowTotal['sum_of_product_amount']);
                $stream->writeCsv($total);
            }
            $downloadedFileName = $fileName;
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
            return $resultRedirect->setPath('*/*/listing');
            
        }
        else if($data['report_type'] == "vendor_wise"){
            $main = [];
            foreach ($collection->getItems() as $key => $value) {
                foreach ($value->getAllVisibleItems() as $value1) {
                    if($value1->getOrderItemVendor()){
                        $productId = $this->product->getIdBySku($value1->getSku());
                        $product = "";
                        $product = $this->product->load($productId);
                        $address = $value->getBillingAddress();
                        $countryId = "";
                        if($address){
                            $countryId = $address->getCountryId();
                        }

                        if($product && $countryId){
                            if(isset($main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'])){
                                if(($value->hasInvoices() || $value->hasShipments()) && $value1->getQtyToRefund()){
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] + $value1->getQtyToRefund();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] + ($value1->getPrice() * $value1->getQtyToRefund());
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] + ($value1->getBaseCost() * $value1->getQtyToRefund());
                                }
                                else{
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] + $value1->getQtyOrdered();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] + ($value1->getPrice() * $value1->getQtyOrdered());
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] = $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] + ($value1->getBaseCost() * $value1->getQtyOrdered());
                                }
                            }
                            else{
                                if(($value->hasInvoices() || $value->hasShipments()) && $value1->getQtyToRefund()){
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] = $value1->getQtyToRefund();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $value1->getPrice() * $value1->getQtyToRefund();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] = $value1->getBaseCost() * $value1->getQtyToRefund();
                                }
                                else{
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_no_of_products'] = $value1->getQtyOrdered();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_amount'] = $value1->getPrice() * $value1->getQtyOrdered();
                                    $main[$value1->getOrderItemVendor()][$countryId][$value->getIncrementId()][$value1->getSku()]['sum_of_product_lp'] = $value1->getBaseCost() * $value1->getQtyOrdered();
                                }
                            }
                        }
                    }
                }
            }
            
            $fileName = "Vendor-".$data['start_date']." TO ".$data['to_date'].'.csv';
            $filepath = 'export/Vendor - '.$fileName;
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

            $directory->create('export');
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();

            $header = ["Vendor Name", 'Country', 'OrderId', 'SKU', 'SUM of No of Products', 'SUM of Product Amount','SUM of Product LP'];
            $stream->writeCsv($header);
            $total = [];
            $rowTotal = [];
            foreach ($main as $key => $value) {

                foreach ($value as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            $csvData = [];
                            $csvData[] = $key;
                            $csvData[] = $key1;
                            $csvData[] = $key2;
                            $csvData[] = $key3;
                            $csvData[] = $value3['sum_of_no_of_products'];
                            $csvData[] = $value3['sum_of_product_amount'];
                            $csvData[] = $value3['sum_of_product_lp'];
                            $rowTotal['sum_of_no_of_products'][] = $value3['sum_of_no_of_products'];
                            $rowTotal['sum_of_product_amount'][] = $value3['sum_of_product_amount'];
                            $rowTotal['sum_of_product_lp'][] = $value3['sum_of_product_lp'];
                            $stream->writeCsv($csvData);
                        }
                    }
                }
            }
            if(isset($rowTotal)){
                $total[] = "Grand Total";
                $total[] = "";
                $total[] = "";
                $total[] = "";
                $total[] = array_sum($rowTotal['sum_of_no_of_products']);
                $total[] = array_sum($rowTotal['sum_of_product_amount']);
                $total[] = array_sum($rowTotal['sum_of_product_lp']);
                $stream->writeCsv($total);
            }

            $downloadedFileName = $fileName;
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
            return $resultRedirect->setPath('*/*/listing');
        }
        else if($data['report_type'] == "category_wise"){
            $configCat = $this->scopeConfig->getValue(
                'report/general/category_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );
            $categoryIds = explode(',', $configCat);
            
            $catNameArr = [];
            foreach ($categoryIds as $key => $value) {
                $category = $this->_categoryFactory->create()->load($value);
                $catNameArr[$value] = $category->getName();
            }
            foreach ($collection->getItems() as $key => $value) {
                foreach ($value->getAllVisibleItems() as $value1) {

                    $productId = $this->product->getIdBySku($value1->getSku());
                    $product = $this->product->load($productId);

                    $productCategory = $this->productCategoryList->getCategoryIds($productId);
                    $productCategory = array_unique($productCategory);
                    $pid = $product->getId();
                    if($pid){
                        if($productCategory){
                            if($product->getQtyOrdered() > 0 || $value1->getQtyToRefund()){
                                $catArr = [];
                                $catArr = array_intersect($categoryIds, $productCategory);
                                if(!empty($catArr)){
                                    foreach ($catArr as $pCat) {
                                        $arr[$value->getId()][] = $product->getSku();
                                        if(isset($main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'])){
                                            if($value->hasInvoices() || $value->hasShipments()){
                                                $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] = $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] + $value1->getQtyToRefund();
                                            }
                                            else{
                                                $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] = $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] + $value1->getQtyOrdered();
                                            }
                                            $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product_amount'] = $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product_amount'] + ($value1->getBaseRowTotal() - $value1->getBaseDiscountAmount());
                                        }
                                        else{
                                            if(($value->hasInvoices() || $value->hasShipments()) && $value1->getQtyToRefund()){
                                                $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] = $value1->getQtyToRefund();
                                            }
                                            else{
                                                $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'] = $value1->getQtyOrdered();
                                            }
                                            $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product_amount'] = $value1->getBaseRowTotal() - $value1->getBaseDiscountAmount();
                                        }
                                        $main[$pCat][$value->getIncrementId()]['sum_of_product_amount_for_avg'][] = $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product_amount'];
                                        $main[$pCat][$value->getIncrementId()]['sum_of_product_for_total'][] = $main[$pCat][$value->getIncrementId()][$product->getSku()]['sum_of_product'];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $fileName = "Category - ".$data['start_date']." TO ".$data['to_date'].'.csv';
            $filepath = 'export/'.$fileName;
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create('export');
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();

            $header = ['Category', 'OrderId', 'SUM of Product Amount','SUM of No of Products','SUM of Product Amount'];
            $stream->writeCsv($header);

            $total = [];
            $total1[] = "Grand Total";
            $total1[] = "";
            foreach ($main as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    $csvData = [];
                    $csvData[] = $catNameArr[$key];
                    $csvData[] = $key1;
                    $csvData[] = array_sum($value1['sum_of_product_amount_for_avg']);
                    $csvData[] = array_sum($value1['sum_of_product_for_total']);
                    $csvData[] = array_sum($value1['sum_of_product_amount_for_avg']) / ((array_sum($value1['sum_of_product_amount_for_avg'])) * 100);
                    
                    $total['sum_of_product_amount_for_avg'][] = array_sum($value1['sum_of_product_amount_for_avg']);
                    $total['sum_of_product_for_total'][] = array_sum($value1['sum_of_product_for_total']);
                    $total['sum_of_product_amount_for_avg_avg'][] = array_sum($value1['sum_of_product_amount_for_avg']) / ((array_sum($value1['sum_of_product_amount_for_avg'])) * 100);
                    $stream->writeCsv($csvData);
                }
            }

            if(count($total)){
                $total1[] = array_sum($total['sum_of_product_amount_for_avg']);
                $total1[] = array_sum($total['sum_of_product_for_total']);
                $total1[] = array_sum($total['sum_of_product_amount_for_avg_avg']);
                $stream->writeCsv($total1);
            }

            $downloadedFileName = $fileName;
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
            
            return $resultRedirect->setPath('*/*/listing');
        }
        else if($data['report_type'] == "refunded"){
            $main = [];
            $total1 = [];
            
            $fileName = "Rufunded - ".$data['start_date']." TO ".$data['to_date'].'.csv';
            $filepath = 'export/'.$fileName;
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create('export');
            $stream = $directory->openFile($filepath, 'w+');
            $stream->lock();
            $header = ['Date', 'OrderId', 'SUM of Refund Amount'];
            $stream->writeCsv($header);

            $connection = $this->resourceConnection->getConnection();
            
            foreach($creditmemoCollection as $creditmemo){

                $result = $connection->fetchAll("SELECT increment_id FROM sales_order WHERE entity_id = " . $creditmemo->getOrderId());
                if (count($result)) {
                    $orderId = $result[0]['increment_id'];
                    $date = date_create($creditmemo->getCreatedAt());
                    $date = date_format($date,"d/m/Y");
                    if(isset($main[$date][$orderId])){
                        $main[$date][$orderId] = $main[$date][$orderId] + $creditmemo->getBaseGrandTotal();
                    }
                    else{
                        $main[$date][$orderId] = $creditmemo->getBaseGrandTotal();
                    }
                }
            }
            foreach ($main as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    $csvData = [];
                    $csvData[] = $key;
                    $csvData[] = $key1;
                    $csvData[] = $value1;
                    $total1[] = $value1;
                    $stream->writeCsv($csvData);
                }
            }

            $total[] = "";
            $total[] = "Grand Total";
            $total[] = array_sum($total1);
            $stream->writeCsv($total);

            $downloadedFileName = $fileName;
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = 1;
            $this->fileFactory->create($downloadedFileName, $content, DirectoryList::VAR_DIR);
            return $resultRedirect->setPath('*/*/listing');
        }
    }
}
