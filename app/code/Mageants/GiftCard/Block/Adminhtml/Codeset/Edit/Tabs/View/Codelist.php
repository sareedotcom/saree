<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Block\Adminhtml\Codeset\Edit\Tabs\View;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data;
use \Mageants\GiftCard\Model\ResourceModel\Codelist\CollectionFactory;
use \Mageants\GiftCard\Model\Codelist as Modelcodelist;
use \Magento\Backend\Model\Session;

class Codelist extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var  \Mageants\GiftCard\Model\ResourceModel\Codelist\CollectionFactory
     */
    protected $_codesetFacotory;

    /**
     * @var  \Mageants\GiftCard\Model\Codelist
     */
    protected $_codesetModel;

    /**
     * @var  \Magento\Backend\Model\Session
     */
    protected $sessionId;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $codesetFacotory
     * @param Modelcodelist $codesetModel
     * @param Session $sessionId
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $codesetFacotory,
        Modelcodelist $codesetModel,
        Session $sessionId,
        array $data = []
    ) {
        $this->_codesetFacotory = $codesetFacotory;
        $this->_codesetModel = $codesetModel;
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
        $this->setId('code_list_id');
        $this->setDefaultSort('code_list_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Add Column Filter To Collection
     *
     * @param object $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        $this->getCollection()->addFieldToFilter($column->getId(), $column->getFilter()->getValue());
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $session=$this->sessionId;
        $codesetid=$session->getCodeId();
        $collection = $this->_codesetFacotory->create();
        $collection->addFieldToFilter('code_set_id', $codesetid);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare column
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'code',
            [
                'header' => __('code'),
                'index' => 'code',
                'class' => 'xxx',
                'width' => '50px',
            ]
        );
        
        $this->addColumn(
            'allocate',
            [
                'header' => __('Allocated'),
                'index' => 'allocate',
                'type' => 'options',
                'options' => [0=>"No", 1=>"Yes"],
            ]
        );
        
        $this->addColumn('action_edit', [
            'header'   =>__('Delete'),
            'width'    => 15,
            'sortable' => false,
            'filter'   => false,
            'type'     => 'action',
            'renderer' => \Mageants\GiftCard\Block\Adminhtml\Codeset\Edit\Tabs\View\Deletelinks::class,
        ]);
        return parent::_prepareColumns();
    }
   
    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/codelist', ['_current' => true]);
    }

    /**
     * Return row url
     *
     * @param  object $row
     * @return string
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
