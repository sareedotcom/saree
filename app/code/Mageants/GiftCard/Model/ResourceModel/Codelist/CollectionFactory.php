<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\ResourceModel\Codelist;

use \Magento\Framework\ObjectManagerInterface;
use \Mageants\GiftCard\Model\ResourceModel\Codelist\Collection;

class CollectionFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;
    
    /**
     * @var \Mageants\GiftCard\Model\ResourceModel\codelist\Collection
     */
    protected $_instanceName = null;
    
    /**
     * @param ObjectManagerInterface $objectManager
     * @param Collection $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Collection::class
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
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
