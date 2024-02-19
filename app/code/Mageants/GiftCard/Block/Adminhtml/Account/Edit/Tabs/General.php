<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
 
namespace Mageants\GiftCard\Block\Adminhtml\Account\Edit\Tabs;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Mageants\GiftCard\Helper\Data;

/**
 * Order General classs
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        $this->_helper=$helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $model = $this->_coreRegistry->registry('account_data');
    
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
        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Category'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('rocktechtemplate_id', 'hidden', ['name' => 'rocktechtemplate_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Account'), 'class' => 'fieldset-wide']
            );
        }

        if ($model->getAccountId()) {
            $fieldset->addField(
                'account_id',
                'hidden',
                [
                    'name' => 'account_id',
                    'id' => 'account_id',
                ]
            );
        }
        
        $fieldset->addField(
            'order_id',
            'label',
            [
                'name' => 'order_id',
                'label' => __('Order Id'),
                'id' => 'order_id',
                'title' => __('Order Id'),
            ]
        );
        
        $fieldset->addField(
            'gift_code',
            'label',
            [
                'name' => 'gift_code',
                'label' => __('Gift Code'),
                'id' => 'gift_code',
                'title' => __('gift Code'),
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'id' => 'status',
                'title' => __('Status'),
                'class' => 'required-entry',
                'required' => true,
                'value'=>'1',
                 'values' => ['0'=>'Inactive','1'=>'Active'],
            ]
        );

        $websites=$this->_helper->getWebsites();
        $fieldset->addField(
            'website',
            'select',
            [
                'name' => 'website',
                'label' => __('Website'),
                'id' => 'website',
                'title' => __('Website'),
                'class' => 'required-entry',
                'required' => true,
                'value'=>'1',
                 'values' => $websites,
            ]
        );
        
        $fieldset->addField(
            'initial_code_value',
            'label',
            [
                'name' => 'initial_code_value',
                'label' => __('Initial Code Value'),
                'id' => 'initial_code_value',
                'title' => __('Initial Code Value'),
            ]
        );

        $fieldset->addField(
            'current_balance',
            'label',
            [
                'name' => 'current_balance',
                'label' => __('Available Balance'),
                'id' => 'initial_code_value',
                'title' => __('Available Balance'),
               ]
        );

        $discountType = $fieldset->addField(
            'discount_type',
            'select',
            [
                'name' => 'discount_type',
                'label' => __('Coupon Discount Type'),
                'id' => 'discount_type',
                'title' => __('Coupon Discount Type'),
                'class' => 'required-entry',
                'required' => true,
                 'values' => ['fixed'=>'Fixed','percent'=>'Percentage'],
            ]
        );
        
        $percentage = $fieldset->addField(
            'percentage',
            'text',
            [
                'name' => 'percentage',
                'label' => __('Percentage'),
                'id' => 'percentage',
                'title' => __('Percentage'),
                'required' => true,
               ]
        );

        $allow_balance = $fieldset->addField(
            'allow_balance',
            'text',
            [
                'name' => 'allow_balance',
                'label' => __('Allow balance'),
                'id' => 'allow_balance',
                'title' => __('Allow balance'),
                'required' => true,
               ]
        );

        $fieldset->addField(
            'expire_at',
            'date',
            [
                'name' => 'expire_at',
                'label' => __('Expire Date'),
                'id' => 'expire_at',
                'title' => __('Expire Date'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'hh:mm:ss'
               ]
        );
        
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'id' => 'comment',
                'title' => __('Comment'),
               ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                    ->addFieldMap($discountType->getHtmlId(), $discountType->getName())
                    ->addFieldMap($percentage->getHtmlId(), $percentage->getName())
                    ->addFieldMap($allow_balance->getHtmlId(), $allow_balance->getName())
                    ->addFieldDependence($percentage->getName(), $discountType->getName(), "percent")
                    ->addFieldDependence($allow_balance->getName(), $discountType->getName(), "fixed")
        );
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
