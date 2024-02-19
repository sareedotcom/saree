<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use \Magento\Store\Model\Website as Storewebsite;

class Website implements ArrayInterface
{
    /**
     * @var Storewebsite
     */
    protected $_website;

    /**
     * @param Storewebsite $website
     */
    public function __construct(
        Storewebsite $website
    ) {
          $this->_website = $website;
    }

    /**
     * Set option for website
     *
     * @return Array
     */
    public function toOptionArray()
    {
        $websites=$this->_website->getCollection();
        $options=[];
        foreach ($websites as $website) {
            $options[$website->getWebsiteId()]=['label'=>$website->getName(),'value'=>$website->getWebsiteId()];
        }
        return $options;
    }
}
