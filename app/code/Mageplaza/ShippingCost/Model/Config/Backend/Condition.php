<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\ShippingCost\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\ShippingCost\Helper\Data;

/**
 * Class Condition
 * @package Mageplaza\ShippingCost\Model\Config\Backend
 */
class Condition extends Value
{
    /**
     * @var RequestInterface
     */
    private $_request;

    /**
     * Condition constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param RequestInterface $request
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        RequestInterface $request,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_request = $request;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     */
    public function beforeSave()
    {
        $data = $this->_convertFlatToRecursive();

        $this->setValue(Data::jsonEncode($data['conditions'][1]));

        return parent::beforeSave();
    }

    /**
     * @return array
     */
    private function _convertFlatToRecursive()
    {
        $arr = [];

        foreach ($this->_request->getParam('rule') as $key => $value) {
            if ($key !== 'conditions' || !is_array($value)) {
                continue;
            }

            foreach ($value as $id => $valueData) {
                $node = &$arr;

                foreach (explode('--', $id) as $iValue) {
                    if (!isset($node[$key][$iValue])) {
                        $node[$key][$iValue] = [];
                    }

                    $node = &$node[$key][$iValue];
                }

                foreach ($valueData as $k => $v) {
                    $node[$k] = $v;
                }
            }
        }

        return $arr;
    }
}
