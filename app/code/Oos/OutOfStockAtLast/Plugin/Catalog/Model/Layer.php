<?php
namespace Oos\OutOfStockAtLast\Plugin\Catalog\Model;

/**
 * Class Layer
 * @package My\Namespace\Plugin\Catalog\Model\Layer
 */
class Layer
{
  /**
  * Sort items that are not salable last
  *
  * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
  */
  public function afterGetProductCollection(
      \Magento\Catalog\Model\Layer $subject,
      \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
  ) {
    
    $orderBy = $collection->getSelect()->getPart(\Zend_Db_Select::ORDER);
    $outOfStockOrderBy = array('is_salable DESC');
    $collection->getSelect()->reset(\Zend_Db_Select::ORDER);
    $collection->getSelect()->order($outOfStockOrderBy);
    return $collection;
  
  }
}