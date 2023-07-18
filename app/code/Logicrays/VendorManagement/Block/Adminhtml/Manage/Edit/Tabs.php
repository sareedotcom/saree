<?php

namespace Logicrays\VendorManagement\Block\Adminhtml\Manage\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Page left menu
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendor_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Vendor Information'));
    }
}
