<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\ResourceModel\Account;

use \Magento\Framework\ObjectManagerInterface;
use \Mageants\GiftCard\Model\ResourceModel\Account\Collection;

/**
 * Account model collection Factory
 */
class CollectionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;
    
    /**
     * @var \Mageants\GiftCard\Model\ResourceModel\Account\Collection
     */
    protected $_instanceName = null;
    
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Mageants\GiftCard\Model\ResourceModel\Account\Collection $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Collection $instanceName
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create the instance data
     *
     * @param array $data
     * @return instance
     */
    public function create(array $data = [])
    {
        return $this->_instanceName;
    }
}
