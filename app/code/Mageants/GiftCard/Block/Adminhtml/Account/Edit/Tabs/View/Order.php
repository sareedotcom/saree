<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Adminhtml\Account\Edit\Tabs\View;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data;
use \Magento\Sales\Model\Order as Modelorder;
use \Magento\Backend\Model\Session;

/**
 * Order classs for Fetch order for Grid
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_account;

    /**
     * Session for Get order Id
     *
     * @var  \Magento\Backend\Model\Session
     */
    protected $sessionId;

   /**
    * @param Context $context
    * @param Data $backendHelper
    * @param Modelorder $account
    * @param Session $sessionId
    * @param array $data
    */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Modelorder $account,
        Session $sessionId,
        array $data = []
    ) {
        $this->_account = $account;
        $this->sessionId = $sessionId;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('id');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Add Column Filter To Collection
     *
     * @param object $column
     */
    protected function _addColumnFilterToCollection($column)
    {
        $this->getCollection()->addFieldToFilter($column->getId(), $column->getFilter()->getValue());
        return $this;
    }

    /**
     * Prepare collection
     */
    protected function _prepareCollection()
    {
        $_sessionId=$this->sessionId;
        $orderid=$_sessionId->getOrderId();
        $collection = $this->_account->getCollection();
        $collection->addFieldToFilter('increment_id', $orderid);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare column for Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('id'),
                'index' => 'entity_id',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchase On'),
                'index' => 'created_at',
                'type' => 'date',
            ]
        );
        
        $this->addColumn(
            'createcustomer_firstname',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_firstname',
                'type' => 'text',
            ]
        );
        
        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Total Amount'),
                'index' => 'base_grand_total',
                'type' => 'text',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Return Grid Url
     *
     * @return $string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/order', ['_current' => true]);
    }

    /**
     * Return Row url
     *
     * @param  object $row
     * @return string=null
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return true;
    }
}
