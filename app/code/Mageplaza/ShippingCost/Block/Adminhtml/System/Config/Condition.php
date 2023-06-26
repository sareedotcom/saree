<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingCost
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingCost\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Block\Conditions;
use Magento\Rule\Model\Condition\AbstractCondition;
use Mageplaza\ShippingCost\Helper\Data;

/**
 * Class Condition
 * @package Mageplaza\ShippingCost\Block\Adminhtml\System\Config
 */
class Condition extends Field
{
    /**
     * @var Fieldset
     */
    private $_rendererFieldset;

    /**
     * @var FormFactory
     */
    private $_formFactory;

    /**
     * @var Conditions
     */
    private $_conditions;

    /**
     * @var RuleFactory
     */
    private $_ruleFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param FormFactory $formFactory
     * @param Fieldset $rendererFieldset
     * @param Conditions $conditions
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        FormFactory $formFactory,
        Fieldset $rendererFieldset,
        Conditions $conditions,
        Data $helperData,
        array $data = []
    ) {
        $this->_ruleFactory      = $ruleFactory;
        $this->_formFactory      = $formFactory;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions       = $conditions;
        $this->helper            = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return mixed|string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var Rule $rule */
        $rule = $this->_ruleFactory->create();

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('rule_');
        $form->setFieldNameSuffix('rule');

        $rule->setData('conditions_serialized', $this->helper->getCondition($this->helper->getStoreId()));

        $formName           = 'catalog_rule_form';
        $conditionsFieldSet = $rule->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'catalog_rule/promo_catalog/newConditionHtml/form/' . $conditionsFieldSet,
            ['form_namespace' => $formName]
        );

        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSet);

        $fieldset = $form->addFieldset('conditions_fieldset', [])->setRenderer($renderer);
        $fieldset->addField('condition', 'text', [])->setRenderer($this->_conditions)->setRule($rule);

        $this->setConditionFormName($rule->getConditions(), $formName, $conditionsFieldSet);

        $customBlock = $this->getLayout()->createBlock(Template::class)
            ->setTemplate('Mageplaza_ShippingCost::rule/conditions.phtml')->toHtml();

        return $customBlock . $fieldset->toHtml();
    }

    /**
     * @param AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     *
     * @return void
     */
    private function setConditionFormName($conditions, $formName, $jsFormName)
    {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
