<?php

namespace MagicToolbox\MagicScroll\Block\Adminhtml\Settings;

/**
 * Backend form container block
 *
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'object_id';
        $this->_controller = 'adminhtml_settings';
        $this->_blockGroup = 'MagicToolbox_MagicScroll';
        $this->_headerText = 'Magic Scroll Config';

        parent::_construct();

        $this->_formScripts[] = '
            var mtErrMessage = \'Error: It seems that your Static Files Cache is outdated. Please, update it so that module\\\'s scripts can be loaded.\';
            var mtRequireConfigMap = null;
            try {
                mtRequireConfigMap = requirejs.s.contexts._.config.map[\'*\'];
            } catch (e) {
                mtRequireConfigMap = null;
            }
            if (mtRequireConfigMap && typeof(mtRequireConfigMap[\'magicscroll\']) == \'undefined\') {
                throw mtErrMessage;
            }
            require([\'magicscroll\'], function(magicscroll){
                if (typeof(magicscroll) == "undefined") {
                    throw mtErrMessage;
                }
                magicscroll.initSettings();
            });
        ';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->updateButton('save', 'label', __('Save Settings'));
    }
}
