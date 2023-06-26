<?php
namespace Potato\Zendesk\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Subject extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('tag', [
            'label' => __('Tag name'),
            'style' => 'width:120px',
        ]);
        $this->addColumn('subject', [
            'label' => __('Subject content'),
            'style' => 'width:310px',
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
