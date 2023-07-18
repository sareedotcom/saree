<?php

namespace Logicrays\VendorManagement\Block\Adminhtml\Manage\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Logicrays\VendorManagement\Model\FeaturedProductsFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Logicrays\VendorManagement\Model\Config\Source\Option\Vendors;

class Products extends Extended
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var FeaturedProductsFactory
     */
    protected $featuredProductsFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var Visibility
     */
    protected $visibility;

    /**
     * @var Vendors
     */
    protected $vendors;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param FeaturedProductsFactory $featuredProductsFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Status $status
     * @param Visibility $visibility
     * @param Vendors $vendors
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        FeaturedProductsFactory $featuredProductsFactory,
        CollectionFactory $productCollectionFactory,
        Status $status,
        Visibility $visibility,
        Vendors $vendors,
        array $data = []
    ) {
        $this->featuredProductsFactory = $featuredProductsFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $registry;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->vendors = $vendors;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * _construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(['in_product' => 1]);
        }
    }

    /**
     * Add Column Filter To Collection
     *
     * @param array $column
     * @return void
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_product') {
            $productIds = $this->_getSelectedProducts();

            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare collection
     *
     * @return array
     */
    protected function _prepareCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToSelect('status');
        $collection->addAttributeToSelect('visibility');
        $collection->addAttributeToSelect('vendor');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_product',
                'align' => 'center',
                'index' => 'entity_id',
                'values' => $this->_getSelectedProducts(),
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'index' => 'price',
                'width' => '50px',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'header_css_class' => 'col-status data-grid-actions-cell',
                'source' => Status::class,
                'options' => $this->status->getOptionArray()
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility data-grid-actions-cell',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'vendor',
            [
                'header' => __('Vendor'),
                'index' => 'vendor',
                'type' => 'options',
                'options' => $this->vendors->getAllVendors(),
                'renderer' => \Logicrays\VendorManagement\Model\Config\Source\Option\VendorsAttValue::class,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsgrid', ['_current' => true]);
    }

    /**
     * Get row url
     *
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Get selected products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $vendorId = $this->getRequest()->getParam('id');
        $vendor = $this->getVendor();
        return $vendor->getProducts($vendorId);
    }

    /**
     * Retrieve selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $vendor = $this->getVendor();
        $vendorId = $this->getRequest()->getParam('id');
        $selected = $vendor->getProducts($vendorId);

        if (!is_array($selected)) {
            $selected = [];
        }
        return $selected;
    }

    /**
     * Return selected vendor data
     *
     * @return array
     */
    protected function getVendor()
    {
        $vendorId = $this->getRequest()->getParam('id');
        $vendor = $this->featuredProductsFactory->create();
        if ($vendorId) {
            $vendor->load($vendorId);
        }
        return $vendor;
    }

    /**
     * Can show tab
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return true;
    }
}
