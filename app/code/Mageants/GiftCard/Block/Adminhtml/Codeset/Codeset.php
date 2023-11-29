<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Adminhtml\Codeset;

/**
 * codeset class for codelist grid
 */
class Codeset extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'code_set_id';
        $this->_blockGroup = 'Mageants_GiftCard';
        $this->_controller = 'adminhtml_codeset';
        parent::_construct();
        $this->buttonList->update('back', 'onclick', "setLocation('" . $this->getUrl('giftcertificate/index/') . "')");
        $this->buttonList->add(
            'saveandcontinue',
            [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
            -100
        );
        $this->buttonList->remove('reset');
    }
 
    /**
     * Retuen Header Text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Add Codeset');
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
     * Return Form Action url
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }

    /**
     * Save and continue url
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
