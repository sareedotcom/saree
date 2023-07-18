<?php

namespace Logicrays\VendorManagement\Block\Adminhtml\Manage\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Store\Model\System\Store;
use Magento\Framework\Registry;
use Magento\Directory\Model\Config\Source\Country;
use Logicrays\VendorManagement\Model\Config\Source\Option\Status;

class Vendor extends Generic implements TabInterface
{
    /**
     * @var FieldFactory
     */
    protected $_fieldFactory;

    /**
     * @var [type]
     */
    protected $_systemStore;

    /**
     * @var Country
     */
    protected $countryFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param Country $countryFactory
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Country $countryFactory,
        Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->countryFactory = $countryFactory;
        $this->status = $status;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Stores array
     *
     * @return array
     */
    public function getStores()
    {
        $stores = $this->_systemStore->getStoreValuesForForm(false, true);
        array_shift($stores);
        return $stores;
    }

    /**
     * Allowed action
     *
     * @param int $resourceId
     * @return boolean
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Prepare form
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
        $vendorForm = $this->_coreRegistry->registry('logicrays_vendor_form');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('vendormanagement_');
        $form->setFieldNameSuffix('vendormanagement');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Vendor Information'),
            'class' => 'fieldset-wide',
        ]);

        if ($vendorForm->getVendorId()) {
            $fieldset->addField(
                'vendor_id',
                'hidden',
                ['name' => 'vendor_id']
            );
        }

        $fieldset->addField('firstname', 'text', [
            'name' => 'firstname',
            'label' => __('First Name'),
            'title' => __('First Name'),
            'required' => true,
        ]);

        $fieldset->addField('lastname', 'text', [
            'name' => 'lastname',
            'label' => __('Last Name'),
            'title' => __('Last Name'),
            'required' => true,
        ]);

        $fieldset->addField('email', 'text', [
            'name' => 'email',
            'label' => __('Email'),
            'title' => __('Email'),
            'required' => true,
            'class' => 'validate-email',
        ]);

        $fieldset->addField('phone', 'text', [
            'name' => 'phone',
            'label' => __('Phone'),
            'title' => __('Phone'),
            'required' => true,
            'class' => 'validate-number',
        ]);

        $fieldset->addField('company', 'text', [
            'name' => 'company',
            'label' => __('Company'),
            'title' => __('Company'),
            'required' => false,
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => $this->status->toOptionArray(),
            'required' => false,
        ]);

        $fieldset = $form->addFieldset('address_fieldset', [
            'legend' => __('Address Information'),
            'class' => 'fieldset-wide',
        ]);

        $fieldset->addField('street', 'text', [
            'name' => 'street',
            'label' => __('Street'),
            'title' => __('Street'),
            'required' => true,
        ]);

        $fieldset->addField('city', 'text', [
            'name' => 'city',
            'label' => __('City'),
            'title' => __('City'),
            'required' => true,
        ]);

        $countries = $this->countryFactory->toOptionArray();
        $countryData = $fieldset->addField(
            'country',
            'select',
            [
                'name' => 'country',
                'title' => __('Country'),
                'label' => __('Country'),
                'values' => $countries,
                'required' => true,
            ]
        );

        $fieldset->addField('state', 'text', [
            'name' => 'state',
            'label' => __('State'),
            'title' => __('State'),
            'required' => true,
        ]);

        $fieldset->addField('zipcode', 'text', [
            'name' => 'zipcode',
            'label' => __('Zipcode'),
            'title' => __('Zipcode'),
            'required' => true,
        ]);

        $fieldset = $form->addFieldset('bank_fieldset', [
            'legend' => __('Banking Information'),
            'class' => 'fieldset-wide',
        ]);

        $fieldset->addField('bank_name', 'text', [
            'name' => 'bank_name',
            'label' => __('Bank Name'),
            'title' => __('Bank Name'),
            'required' => false,
        ]);

        $fieldset->addField('account_no', 'text', [
            'name' => 'account_no',
            'label' => __('Account No'),
            'title' => __('Account No'),
            'required' => false,
            'class' => 'validate-number',
        ]);

        $fieldset->addField('branch_name', 'text', [
            'name' => 'branch_name',
            'label' => __('Branch Name'),
            'title' => __('Branch Name'),
            'required' => false,
        ]);

        $fieldset->addField('ifsc_code', 'text', [
            'name' => 'ifsc_code',
            'label' => __('Ifsc Code'),
            'title' => __('Ifsc Code'),
            'required' => false,
        ]);

        $fieldset->addField('gst_no', 'text', [
            'name' => 'gst_no',
            'label' => __('GST No'),
            'title' => __('GST No'),
            'required' => false,
        ]);

        $vendorData = $this->_session->getData('logicrays_vendor_form', true);

        if ($vendorData) {
            $vendorForm->addData($vendorData);
        } else {
            if (!$vendorForm->getId()) {
                $vendorForm->addData($vendorForm->getDefaultValues());
            }
        }

        $form->addValues($vendorForm->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
