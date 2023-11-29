<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Adminhtml\Template;

/**
 * Template class for Gift template
 */
class Template extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'image_id';
        $this->_blockGroup = 'Mageants_GiftCard';
        $this->_controller = 'adminhtml_template';
        parent::_construct();
        $this->buttonList->update('back', 'onclick', "setLocation('" .
            $this->getUrl('giftcertificate/gcimages/') . "')");
        
        $this->buttonList->remove('reset');
    }
    
    /**
     * Return Header text
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
     * Return Form action url
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
