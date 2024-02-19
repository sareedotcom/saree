<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use \Magento\Catalog\Model\Category;
use \Magento\Store\Model\StoreManagerInterface;
use \Mageants\GiftCard\Helper\Data;

/**
 *    configure product when update from cart
 */
class CheckStatus implements ObserverInterface
{
    /**
     * @var Pool
     */
    public $cacheFrontendPool;

    /**
     * @var TypeListInterface
     */
    public $cacheTypeList;

    /**
     * @var \Magento\Catalog\Model\Category $category
     */
    protected $_category;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $_storeManager;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Category $category
     * @param StoreManagerInterface $storeManager
     * @param Data $data
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        Category $category,
        StoreManagerInterface $storeManager,
        Data $data,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->_category=$category;
        $this->_storeManager=$storeManager;
        $this->data=$data;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Configure product and update cart
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $cat_info = $this->_category->load($this->_storeManager->getStore()->getRootCategoryId());
           $cate=$this->_category->getCollection()->addAttributeToFilter('url_key', 'giftcard')->getFirstItem();
        if ($cate->getId()) {
                $_gcstatus=$this->data->checkStatus();
                $status = ($_gcstatus == 1) ? true : false;
                $_updateCat=$this->_category->load($cate->getId());
                $_updateCat->setIsActive($status);
                $_updateCat->setId($cate->getId());
                // $_updateCat->save();
                $invalidatedTypes = $this->cacheTypeList->getInvalidated();
            if (isset($invalidatedTypes)) {
                foreach ($invalidatedTypes as $value) {
                    if ($value["id"] == "layout" || $value["id"] == "full_page") {
                        $this->cacheTypeList->cleanType($value["id"]);
                    }
                }
            }
        }
    }
}
