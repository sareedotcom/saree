<?php

namespace Logicrays\VendorManagement\Controller\Adminhtml\Manage;

use Logicrays\VendorManagement\Model\VendorManagementFactory;
use Logicrays\VendorManagement\Model\FeaturedProductsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class adminhtml vendor save action
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Js
     */
    protected $_jsHelper;

    /**
     * @var Session
     */
    protected $_adminSession;

    /**
     * @var VendorManagementFactory
     */
    protected $vendorFactory;

    /**
     * @var FeaturedProductsFactory
     */
    protected $featuredProductsFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var ProductAction
     */
    protected $productAction;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param Context $context
     * @param Js $jsHelper
     * @param Session $adminSession
     * @param VendorManagementFactory $vendorFactory
     * @param FeaturedProductsFactory $featuredProductsFactory
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param ResourceConnection $resources
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Request $request
     * @param CollectionFactory $collection
     * @param ProductAction $action
     */
    public function __construct(
        Context $context,
        Js $jsHelper,
        Session $adminSession,
        VendorManagementFactory $vendorFactory,
        FeaturedProductsFactory $featuredProductsFactory,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        ResourceConnection $resources,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Request $request,
        CollectionFactory $collection,
        ProductAction $action
    ) {
        $this->_jsHelper = $jsHelper;
        $this->_adminSession = $adminSession;
        $this->vendorFactory = $vendorFactory;
        $this->featuredProductsFactory = $featuredProductsFactory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->_resources = $resources;
        $this->uploaderFactory = $uploaderFactory;
        $this->_filesystem = $filesystem;
        $this->request = $request;
        $this->productCollection = $collection;
        $this->productAction = $action;
        parent::__construct($context);
    }

    /**
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $vendorData = $data['vendormanagement'];
        $resultRedirect = $this->resultRedirectFactory->create();

        if (isset($vendorData['vendor_id'])) {
            $vendorId = $vendorData['vendor_id'];
            $vendor = $this->vendorFactory->create();
            $vendor->load($vendorId);
            $vendor->setData($vendorData);
            $vendor->save();
            if (array_key_exists("products", $data)) {
                $productsData = $data['products'];
                $model = $this->featuredProductsFactory->create();
                $this->saveProducts($vendorId, $model, $productsData);
            }

            $this->messageManager->addSuccess(__('You saved this vendor.'));
            $this->_adminSession->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $vendorId, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        } else {
            $vendor = $this->vendorFactory->create();
            $vendor->setData($vendorData);
            $vendor->save();

            if (array_key_exists("products", $data)) {
                $productsData = $data['products'];
                $model = $this->featuredProductsFactory->create();
                $this->saveProducts($vendor->getVendorId(), $model, $productsData);
            }

            $this->messageManager->addSuccess(__('You saved this vendor.'));
            $this->_adminSession->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $vendor->getVendorId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save products action
     *
     * @param int $vendorId
     * @param array $model
     * @param [type] $post
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function saveProducts($vendorId, $model, $post)
    {
        $productIds = $this->_jsHelper->decodeGridSerializedInput($post);
        try {
            $model = $this->featuredProductsFactory->create();
            $model = $model->load($vendorId);
            $oldProducts = (array) $model->getProducts($vendorId);
            $newProducts = (array) $productIds;
            $connection = $this->_resources->getConnection();
            $table = $this->_resources->getTableName(
                \Logicrays\VendorManagement\Model\ResourceModel\FeaturedProducts::ENTITY_NAME
            );
            $insert = array_diff($newProducts, $oldProducts);
            $delete = array_diff($oldProducts, $newProducts);
            if (!empty($delete)) {
                $this->setDefaultAttrValue($delete);
                $where = ['vendor_id = ?' => $vendorId, 'product_id IN (?)' => $delete];
                $connection->delete($table, $where);
            }
            $model = $this->featuredProductsFactory->create();
            if (!empty($insert)) {
                $this->updateProductAttribute($vendorId, $insert);
                $oldAssignedVendors = (array) $model->getAlreadyAssignedProducts($insert);
                $oldAssignedVendorProducts = array_column($oldAssignedVendors, 'product_id');
                $insertNewRecords = array_diff($insert, $oldAssignedVendorProducts);
                foreach ($oldAssignedVendors as $oldAssignedVendor) {
                    $where = ['vendor_id = ?' => $oldAssignedVendor['vendor_id'], 'product_id IN (?)' => $oldAssignedVendor['product_id']];
                    $connection->update($table, ['vendor_id' => $vendorId], $where);
                }
                if (!empty($insertNewRecords)) {
                    $data = [];
                    foreach ($insertNewRecords as $product_id) {
                        $data[] = ['vendor_id' => $vendorId, 'product_id' => (int) $product_id];

                    }
                    $connection->insertMultiple($table, $data);
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the vendor.'));
        }
    }

    /**
     * Update product attribute
     *
     * @param int $vendorId
     * @param array $productIds
     * @return void
     */
    public function updateProductAttribute($vendorId, $productIds)
    {
        if (!empty($productIds)) {
            $this->productAction->updateAttributes($productIds, array('vendor' => $vendorId), '1');
        }
    }

    /**
     * Set default attr value
     *
     * @param array $productIds
     * @return void
     */
    public function setDefaultAttrValue($productIds)
    {
        if (!empty($productIds)) {
            $this->productAction->updateAttributes($productIds, array('vendor' => ''), '1');
        }
    }
}
