<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\Config\Source;

class Message extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Message value to set in Option
     *
     * @return Array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options=  [
                ['value' => 0, 'label' => __('No')],
                ['value' => 1, 'label' => __('Yes')]
            ];
        }
        return $this->_options;
    }
}
