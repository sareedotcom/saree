<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Model\Config\Source;

use \Magento\Catalog\Model\CategoryFactory;
use \Magento\Catalog\Model\Category;
use Magento\Framework\Controller\ResultFactory;

/**
 * Categories classs for category array
 */
class Categories extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;
  
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_categories;

    /**
     * @param CategoryFactory $categoryFactory
     * @param Category $categories
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        Category $categories
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_categories = $categories;
    }
  
    /**
     * Return all option
     *
     * @return Array
     */
    public function getAllOptions()
    {
        $collection = $this->_categoryFactory->create()->getCollection()->addFieldToFilter('is_active', 1);
        $options=[];
        if ($collection->getSize()) {
            foreach ($collection as $template) {
                $cat=$this->_categories->load($template->getEntityId());
                if ($cat->getName()!='Gift Card'):
                    $options[$template->getEntityId()]=['value'=>$template->getEntityId(),'label'=>$cat->getName()];
                endif;
            }
        }
        return $options;
    }
}
