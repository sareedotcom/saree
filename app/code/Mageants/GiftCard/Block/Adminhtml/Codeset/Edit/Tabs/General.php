<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Adminhtml\Codeset\Edit\Tabs;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('code_set_data');
        $form = $this->_formFactory->create(
            ['data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post'
                        ]
            ]
        );
      
        $form->setHtmlIdPrefix('rock_');
        if ($model->getCodeSetId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Codeset'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('rocktechtemplate_id', 'hidden', ['name' => 'rocktechtemplate_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Codeset'), 'class' => 'fieldset-wide']
            );
        }
        if ($model->getCodeSetId()) {
            $fieldset->addField(
                'code_set_id',
                'hidden',
                [
                    'name' => 'code_set_id',
                    'id' => 'code_set_id',
                ]
            );
        }
        $fieldset->addField(
            'code_title',
            'text',
            [
                'name' => 'code_title',
                'label' => __('Code Title'),
                'id' => 'code_title',
                'title' => __('Code Title'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
        $fieldset->addField(
            'code_pattern',
            'text',
            [
                'name' => 'code_pattern',
                'label' => __('Code Pattern'),
                'id' => 'code_pattern',
                'title' => __('Code Pattern'),
                'class' => 'required-entry',
                'required' => true,
                'note'=>'{L} - letter, {D} - digit e.g. PROMO_{L}{L}{D}{D}{D} results in PROMO_DF627'
            ]
        );
        $fieldset->addField(
            'code_qty',
            'text',
            [
                'name' => 'code_qty',
                'label' => __('Code Qty'),
                'id' => 'code_qty',
                'title' => __('Code Qty'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );
         
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Information');
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
        return false;
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
}
