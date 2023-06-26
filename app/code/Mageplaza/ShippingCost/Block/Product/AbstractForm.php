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

namespace Mageplaza\ShippingCost\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Mageplaza\ShippingCost\Helper\Data;
use Mageplaza\ShippingCost\Model\Config\Source\Position;

/**
 * Class AbstractForm
 * @package Mageplaza\ShippingCost\Block\Product
 */
class AbstractForm extends Template
{
    const POSITION = '';

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * AbstractForm constructor.
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Data $helper,
        RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->registry    = $registry;
        $this->helper      = $helper;
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $data);

        $this->setTabData();
    }

    /**
     * @return $this
     */
    public function setTabData()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        $product = $this->getProduct();
        $rule    = $this->ruleFactory->create();

        $rule->setConditionsSerialized($this->helper->getCondition());

        return $product->isVirtual()
            || !$this->helper->isEnabled()
            || !in_array(static::POSITION, $this->helper->getPosition(), true)
            || !$rule->getConditions()->validate(clone $product);
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $this->jsLayout = [
            'components' => [
                'mpshippingcost-form' => [
                    'component' => 'Mageplaza_ShippingCost/js/view/form',
                    'config'    => [
                        'title'       => $this->helper->getTitle(),
                        'description' => $this->helper->getDescription(),
                        'countries'   => $this->getCountries(),
                        'allRegions'  => $this->helper->getAllRegions(true),
                        'calcUrl'     => $this->getUrl('mpshippingcost/index/calculate'),
                        'fields'      => $this->helper->getFields(),
                        'popup'       => $this->helper->getPopup(),
                        'notFoundMsg' => $this->helper->getNotFoundMsg(),
                        'ruleAddress' => $this->getDefaultAddress(),
                    ]
                ]
            ]
        ];

        return parent::getJsLayout();
    }

    /**
     * @return array
     */
    private function getCountries()
    {
        $allCountries = $this->helper->getCountries(true);

        if (!$country = $this->helper->getCountry()) {
            return $allCountries;
        }

        $result = [];

        foreach ($allCountries as $item) {
            if (in_array($item['value'], $country, true)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getDefaultAddress()
    {
        $address = new DataObject($this->helper->getGeoIpData());

        return [
            'country'  => $address->getData('country_id') ?: $this->helper->getDefaultCountry(),
            'region'   => $address->getData('region') ?: $this->helper->getDefaultRegion(),
            'regionId' => $address->getData('region_id') ?: $this->helper->getDefaultRegionId(),
            'postcode' => $address->getData('postcode') ?: $this->helper->getDefaultPostcode(),
        ];
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->registry->registry('product');
        }

        return $this->_product;
    }

    /**
     * @return bool
     */
    public function canShowTitle()
    {
        return static::POSITION === Position::ADDITIONAL_TAB && $this->helper->getTitle();
    }
}
