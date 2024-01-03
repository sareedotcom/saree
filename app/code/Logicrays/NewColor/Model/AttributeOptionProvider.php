<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Logicrays\NewColor\Model;

use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ProductFactory;
use Logicrays\NewColor\Helper\Data;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;

/**
 * Provider for retrieving configurable options.
 */
class AttributeOptionProvider extends \Magento\ConfigurableProduct\Model\AttributeOptionProvider
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @var OptionSelectBuilderInterface
     */
    private $optionSelectBuilder;

    /**
     * @param Attribute $attributeResource
     * @param ScopeResolverInterface $scopeResolver,
     * @param OptionSelectBuilderInterface $optionSelectBuilder
     * @param Data $helperData
     */
    public function __construct(
        Attribute $attributeResource,
        ScopeResolverInterface $scopeResolver,
        OptionSelectBuilderInterface $optionSelectBuilder,
        Data $helperData
    ) {
        $this->attributeResource = $attributeResource;
        $this->scopeResolver = $scopeResolver;
        $this->optionSelectBuilder = $optionSelectBuilder;
        $this->helperData = $helperData;
        parent::__construct($attributeResource,$scopeResolver,$optionSelectBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptions(AbstractAttribute $superAttribute, $productId)
    {
        $scope  = $this->scopeResolver->getScope();

        $select = $this->optionSelectBuilder->getSelect($superAttribute, $productId, $scope);

        $isSortingAvailable = $this->helperData->getCustomSorting($productId);
        $sortingSku = "";
        if($isSortingAvailable){
            $arr = explode(",",$isSortingAvailable);
            foreach ($arr as $value) {
                $sortingSku .= "'".$value."',";
            }
            $sortingSku = rtrim($sortingSku, ',');
        }
        if($isSortingAvailable && $sortingSku){
            $select->order(new \Zend_Db_Expr('FIELD(`entity`.`sku`,'. $sortingSku.')'));
        }
        $data = $this->attributeResource->getConnection()->fetchAll($select);

        if ($superAttribute->getSourceModel()) {
            $options = $superAttribute->getSource()->getAllOptions(false);

            $optionLabels = [];
            foreach ($options as $option) {
                $optionLabels[$option['value']] = $option['label'];
            }

            foreach ($data as $key => $value) {
                $optionText = isset($optionLabels[$value['value_index']])
                    ? $optionLabels[$value['value_index']]
                    : false;
                $data[$key]['default_title'] = $optionText;
                $data[$key]['option_title'] = $optionText;
            }
        }

        return $data;
    }
}
