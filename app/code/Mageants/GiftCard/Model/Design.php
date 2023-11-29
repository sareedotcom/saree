<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model;

/**
 * Init Model class
 */
class Design extends \Magento\Catalog\Model\Design
{
    /**
     * Get custom layout settings
     *
     * @param \Magento\Catalog\Model\Category|\Magento\Catalog\Model\Product $object
     * @return \Magento\Framework\DataObject
     */
    public function getDesignSettings($object)
    {
        if ($object instanceof \Magento\Catalog\Model\Product) {
            $currentCategory = $object->getCategory();
            if ($currentCategory != null) {
                $currentCategory=null;
            }
        } else {
            $currentCategory = $object;
        }

        $category = null;
        if ($currentCategory) {
            $category = $currentCategory->getParentDesignCategory($currentCategory);
        }

        if ($object instanceof \Magento\Catalog\Model\Product) {
            if ($category && $category->getCustomApplyToProducts()) {
                return $this->_mergeSettings($this->_extractSettings($category), $this->_extractSettings($object));
            } else {
                return $this->_extractSettings($object);
            }
        } else {
            return $this->_extractSettings($category);
        }
    }
}
