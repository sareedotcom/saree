<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Block\Adminhtml\Account;

/**
 * Account  classs for Order account
 */
class Account extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _construct()
    {
        $this->_objectId = 'account_id';
        $this->_blockGroup = 'Mageants_GiftCard';
        $this->_controller = 'adminhtml_account';
        parent::_construct();
        $this->buttonList->update('back', 'onclick', "setLocation('" .
            $this->getUrl('giftcertificate/gcaccount/') . "')");
        $this->buttonList->add(
            'saveandcontinue',
            [
                    'label' => __('Save and Resend Email'),
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
     * Return Header text
     *
     * @return $string
     */
    public function getHeaderText()
    {
        return __('Add Account');
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
     * Return Url Form Action
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
