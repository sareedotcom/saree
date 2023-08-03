<?php

namespace Logicrays\VendorManagement\Block\Adminhtml\Manage;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Add header button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'vendor_id';
        $this->_blockGroup = 'Logicrays_VendorManagement';
        $this->_controller = 'adminhtml_manage';
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Vendor'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ],
            ],
            -100
        );

        $this->removeButton("reset");
        $this->removeButton("delete");
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('logicrays_vendor_form')->getId()) {
            return __(
                "Edit Post '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('logicrays_vendor_form')->getFirstname())
            );
        } else {
            return __('New Record');
        }
    }

    /**
     * SaveAndContinue URL getter
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{id}}']);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
        function toggleEditor() {
            if (tinyMCE.getInstanceById('page_content') == null) {
                tinyMCE.execCommand('mceAddControl', false, 'content');
            } else {
                tinyMCE.execCommand('mceRemoveControl', false, 'content');
            }
        };
        ";
        return parent::_prepareLayout();
    }
}
