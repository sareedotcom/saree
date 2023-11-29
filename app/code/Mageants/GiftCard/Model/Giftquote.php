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
 * GiftQuote  model class
 */
class Giftquote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init of model
     */
    protected function _construct()
    {
        $this->_init(\Mageants\GiftCard\Model\ResourceModel\Giftquote::class);
    }
}
