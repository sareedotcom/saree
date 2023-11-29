<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\ResourceModel\Codeset;

use \Magento\Framework\ObjectManagerInterface;
use \Mageants\GiftCard\Model\ResourceModel\Codeset\Collection;

/**
 * Codeset model collection Factory
 */
class CollectionFactory
{
    /**
     * @var \Mageants\GiftCard\Model\ResourceModel\Codeset\Collection
     */
    protected $_instanceName = null;
    
    /**
     * Construct
     *
     * @param Collection $instanceName
     */
    public function __construct(
        Collection $instanceName
    ) {
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
