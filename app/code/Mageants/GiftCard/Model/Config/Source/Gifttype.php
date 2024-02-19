<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Config\Source;

class Gifttype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Return Gifttype option to set
     *
     * @return Array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options=  [
                ['value' => 0, 'label' => __('Virtual')],
                ['value' => 1, 'label' => __('Printed')],
                ['value' => 2, 'label' => __('Combined')]
            ];
        }
        return $this->_options;
    }
}
