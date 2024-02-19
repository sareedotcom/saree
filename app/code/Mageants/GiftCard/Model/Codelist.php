<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model;

use Magento\Framework\Exception\LocalizedException as CoreException;

/**
 * CodeList Model class
 */
class Codelist extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init Model class
     */
    protected function _construct()
    {
        $this->_init(\Mageants\GiftCard\Model\ResourceModel\Codelist::class);
    }
}
