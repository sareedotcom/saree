<?php
namespace Elsner\Productposition\Block\Adminhtml\Productposition\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productposition_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Productposition Information'));
    }
}