<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Model\CartRulesValidator;

use Zend_Validate_Exception;

/**
 * Class Discount
 *
 * Ignore discount from cart rules during order editing
 */
class Discount implements \Zend_Validate_Interface
{
    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (!$value instanceof \Magento\Quote\Model\Quote\Item\AbstractItem) {
            return true;
        }

        if ($value->getIgnoreCartRules()) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false. The array keys are validation failure message identifiers,
     * and the array values are the corresponding human-readable message strings.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @return array
     */
    public function getMessages()
    {
        return [];
    }
}
