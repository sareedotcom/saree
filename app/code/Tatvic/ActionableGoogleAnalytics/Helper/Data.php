<?php

namespace Tatvic\ActionableGoogleAnalytics\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Magento\Backend\Model\Auth\Session;

/**
 * Class Data
 * @package Tatvic\ActionableGoogleAnalytics\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_tvc_ga_options;

    protected $tvc_categoryCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $tvc_registry;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $tvc_cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $tvc_cookieMetaData;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $listProduct;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $_toolbar;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Repository
     */
    protected $productAttributeRepository;

    /**
     * @var \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    protected $productListBlockToolbar;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var string
     */
    protected $connect_url = "";

    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepos;

    /**
     * @var BestSellersCollectionFactory
     */
    protected $_bestSellersCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    protected $stockState;

    /**
     * @var Session
     */
    private $backendSession;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetaData
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Catalog\Block\Product\ListProduct $listProduct
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     * @param \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $productListBlockToolbar
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Catalog\Model\ProductFactory $_productFactory
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_productCollectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepos
     * @param BestSellersCollectionFactory $bestSellersCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetaData,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $productListBlockToolbar,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $_productCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepos,
        BestSellersCollectionFactory $bestSellersCollectionFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        Session $backendSession,
        array $data = []
    ) {
        parent::__construct($context);
        $this->_tvc_ga_options = $this->scopeConfig->getValue('tatvic_ee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->tvc_registry = $registry;
        $this->tvc_categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->cart = $cart;
        $this->order = $order;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->storeManager    = $storeManager;
        $this->customerSession = $customerSession;
        $this->tvc_cookieManager = $cookieManager;
        $this->tvc_cookieMetaData = $cookieMetaData;
        $this->sessionManager = $sessionManager;
        $this->listProduct = $listProduct;
        $this->_toolbar = $toolbar;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productListBlockToolbar = $productListBlockToolbar;
        $this->collectionFactory = $collectionFactory;
        $this->queryFactory = $queryFactory;
        $this->_productFactory = $_productFactory;
        $this->_stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
        $this->_productRepos = $productRepos;
        $this->_bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->stockState = $stockState;
        $this->backendSession = $backendSession;
    }

    public function getPublicKey()
    {
        return $this->_tvc_ga_options['purchase_code']['purchase_code'];
    }
    public function isEnabled()
    {
        return $this->_tvc_ga_options['general']['enable'];
    }
    public function mode()
    {
        return $this->_tvc_ga_options['general']['tracking_type'];
    }
    public function method()
    {
        return $this->_tvc_ga_options['general']['list_mode'];
    }
    public function getUaID()
    {
        return $this->_tvc_ga_options['general']['ua_id_ga'];
    }
    public function getMeasurmentIDGA4()
    {
        $measurment_id_ga4 = isset($this->_tvc_ga_options['general']['measurment_id_ga4']) ? $this->_tvc_ga_options['general']['measurment_id_ga4'] : '';
        return $measurment_id_ga4;
    }
    public function getUaIDBoth()
    {
        $bothua = isset($this->_tvc_ga_options['general']['ua_id_both']) ? $this->_tvc_ga_options['general']['ua_id_both'] : '';
        return $bothua;
    }
    public function getMeasurmentIdBoth()
    {
        $measurmentidboth = isset($this->_tvc_ga_options['general']['measurment_id_both']) ? $this->_tvc_ga_options['general']['measurment_id_both'] : '';
        return $measurmentidboth;
    }
    public function emailID()
    {
        return $this->_tvc_ga_options['general']['email_id'];
    }
    public function referenceToken()
    {
        return $this->_tvc_ga_options['general']['ref_token'];
    }
    public function getGtm()
    {
        return $this->_tvc_ga_options['general']['gtm'];
    }
    public function checkDf_enabled()
    {
        return $this->_tvc_ga_options['advance']['enableDF'];
    }
    public function checkIP_enabled()
    {
        return $this->_tvc_ga_options['advance']['enableIP'];
    }
    public function checkUserId_Enabled()
    {
        return $this->_tvc_ga_options['advance']['enableUserID'];
    }
    public function checkClientId_Enabled()
    {
        return $this->_tvc_ga_options['advance']['enableClientID'];
    }

    public function getLinkAttribution()
    {
        return $this->_tvc_ga_options['advance']['enableClientID'];
    }

    public function getDisableAdsFeature()
    {
        return $this->_tvc_ga_options['advance']['adsSign'];
    }
    public function checkOptOut_Enabled()
    {
        return $this->_tvc_ga_options['advance']['linkAttr'];
    }
    public function formFieldTracking_Enable()
    {
        return $this->_tvc_ga_options['advance']['formTracking'];
    }
    public function getContentGrouping()
    {
        return $this->_tvc_ga_options['advance']['enableContentGroup'];
    }
    public function getOptimizeIP()
    {
        return $this->_tvc_ga_options['advance']['optimize_id'];
    }
    public function checkAdwords_Enabled()
    {
        return $this->_tvc_ga_options['conversion']['advenable'];
    }
    public function checkFbpixelEnabled()
    {
        return $this->_tvc_ga_options['conversion']['fbPixelenable'];
    }
    public function getAdwordsID()
    {
        return $this->_tvc_ga_options['conversion']['adv_id'];
    }
    public function getAdwordsLabel()
    {
        return $this->_tvc_ga_options['conversion']['adv_label'];
    }
    public function getHpCategoryID()
    {
        return $this->_tvc_ga_options['configuration']['hp_cat'];
    }

    public function getHpBrandCode()
    {
        return $this->_tvc_ga_options['configuration']['brand_code'];
    }

    /**
     * is login admin user
     *
     * @return bool
     */
    public function isAdminLogin(): bool
    {
        return $this->backendSession->getUser() && $this->backendSession->getUser()->getId();
    }

    public function getBestSeller()
    {
        return $this->_tvc_ga_options['configuration']['best_seller'];
    }

    public function getBestSellerPeriod()
    {
        return $this->_tvc_ga_options['configuration']['period'];
    }

    public function getFBPixelID()
    {
        $fb_id = !empty($this->_tvc_ga_options['conversion']['fb_id']) ? $this->_tvc_ga_options['conversion']['fb_id'] : '';
        return $fb_id;
    }

    protected function getCurrentProduct()
    {
        return $this->tvc_registry->registry('current_product');
    }

    public function getCurrentCategory()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Framework\Registry')->registry('current_category');
    }

    protected function getCurrentCategoryID()
    {
        return $this->tvc_registry->registry('current_category')->getId();
    }
    protected function getCurrentCategoryName()
    {
        return $this->tvc_registry->registry('current_category')->getName();
    }
    protected function setPageLimit()
    {
        $pageSize = $this->productListBlockToolbar->getLimit() ? $this->productListBlockToolbar->getLimit() : 9;
        return $pageSize;
    }
    protected function getCurrentPage()
    {
        $page = ($this->_request->getParam('p')) ? $this->_request->getParam('p') : 1;
        return $page;
    }

    protected function getCategoryByIds($tvc_cat_ids)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tvc_cat_names = [];
        if (is_array($tvc_cat_ids)) {
            foreach ($tvc_cat_ids as $category) {
                $cat = $objectManager->create('Magento\Catalog\Model\Category')->load($category);
                $tvc_cat_names[] = $cat->getName();
            }
            $tvc_category = implode('/', $tvc_cat_names);

            return $tvc_category;
        }
    }

    /**
     * Get category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory($categoryId)
    {
        $this->_category = $this->categoryFactory->create();
        $this->_category->load($categoryId);
        return $this->_category;
    }

    /**
     * Undocumented function
     * Get all Category Products
     * @return array
     */
    public function getCategoryProduct()
    {
        $tvc_catProd = [];
        $tvc_cat_name = $this->getCurrentCategoryName();
        $tvc_cat_id   = $this->getCurrentCategoryID();
        $tvc_category   = $this->categoryFactory->create()->load($tvc_cat_id);
        $tvc_collection = $this->tvc_categoryCollectionFactory->create();
        $tvc_collection->addAttributeToSelect('*');
        $tvc_collection->addCategoryFilter($tvc_category);
        if ($this->_request->getParam('product_list_order')) {
            $tvc_collection->addAttributeToSort($this->_request->getParam('product_list_order'));
        } else {
            $tvc_collection->setOrder('position');
        }
        $tvc_collection->setCurPage($this->_toolbar->getCurrentPage());
        $tvc_collection->setPageSize($this->setPageLimit());
        $tvc_metaData = $this->tvc_cookieMetaData->createPublicCookieMetadata()->setPath($this->sessionManager->getCookiePath());
        $this->tvc_cookieManager->setPublicCookie('tvc_list', 'Category Page', $tvc_metaData);

        foreach ($tvc_collection as $product) {
            $stockItem = $product->getExtensionAttributes()->getStockItem();

            if (empty($stockItem)) {
                $stockQty = 0;
            } else {
                $stockQty = $stockItem->getQty();
            }

            if ($product->isInStock() == 1) {
                $stock = "in_stock";
            } else {
                $stock = "out_of_stock";
            }
            $prod_arr = [
                'tvc_id'   => $product->getId(),
                'tvc_i'    => $product->getSku(),
                'tvc_name' => $product->getName(),
                'tvc_p'    => $product->getFinalPrice(),
                'tvc_url'  => $product->getProductUrl(),
                'tvc_c'    => $tvc_cat_name,
                'tvc_ss'   => $stock,
                'tvc_st'   => $stockQty
            ];
            array_push($tvc_catProd, $prod_arr);
        }
        return $tvc_catProd;
    }

    public function getStockItem($productId)
    {
        return $this->_stockItemRepository->get($productId);
    }

    public function getProductDetails()
    {
        $tvc_cat_ids = $this->getCurrentProduct()->getCategoryIds();
        $tvc_category = $this->getCategoryByIds($tvc_cat_ids);
        $tvc_metaData = $this->tvc_cookieMetaData->createPublicCookieMetadata()->setPath($this->sessionManager->getCookiePath());
        $size = $this->getCurrentProduct()->getResource()->getAttribute('color');
        
        $stockItem = $this->getCurrentProduct()->getExtensionAttributes()->getStockItem();

        if (empty($stockItem)) {
            $stockQty = 0;
        } else {
            $stockQty = $stockItem->getQty();
        }
        if ($this->getCurrentProduct()->getIsInStock() == true) {
            $stock = "in_stock";
        } else {
            $stock = "out_of_stock";
        }
        return [
            'tvc_id' => $this->getCurrentProduct()->getId(),
            'tvc_i' => $this->getCurrentProduct()->getSku(),
            'tvc_name' => $this->getCurrentProduct()->getName(),
            'tvc_p' => $this->getCurrentProduct()->getFinalPrice(),
            'tvc_c' => $tvc_category,
            'tvc_stk' => $stockQty,
            'tvc_ss' => $stock
        ];
    }

    public function getRalatedProducts()
    {
        $relProducts = [];
        $currentProduct = $this->getCurrentProduct();
        $relatedProducts = $currentProduct->getRelatedProducts();

        if (!empty($relatedProducts)) {
            foreach ($relatedProducts as $relatedProduct) {
                $product = $this->productRepository->get($relatedProduct->getSku());
                if ($product->isInStock() == 1) {
                        $stock = "in_stock";
                    } else {
                        $stock = "out_of_stock";
                    }
                $tvc_cat_ids = $product->getCategoryIds();
                $tvc_category = $this->getCategoryByIds($tvc_cat_ids);
                $productRel = [
                    'tvc_id' => $product->getId(),
                    'tvc_i'    => $product->getSku(),
                    'tvc_name' => $product->getName(),
                    'tvc_p'    => $product->getFinalPrice(),
                    'tvc_url'  => $product->getProductUrl(),
                    'tvc_list' =>  "related_products",
                    'tvc_c' => $tvc_category,
                    'tvc_ss' => $stock
                ];
                array_push($relProducts, $productRel);
            }
        }
        return $relProducts;
    }

    public function getUpSellProducts(){
        $upProducts = [];
        $currentProduct = $this->getCurrentProduct();
        $upSellProducts = $currentProduct->getUpSellProducts();
        if (!empty($upSellProducts)) {
            foreach ($upSellProducts as $upSellProduct) {
                $product = $this->productRepository->get($upSellProduct->getSku());
                if ($product->isInStock() == 1) {
                        $stock = "in_stock";
                    } else {
                        $stock = "out_of_stock";
                    }
                $tvc_cat_ids = $product->getCategoryIds();
                $tvc_category = $this->getCategoryByIds($tvc_cat_ids);
                $productRel = [
                    'tvc_id' => $product->getId(),
                    'tvc_i'    => $product->getSku(),
                    'tvc_name' => $product->getName(),
                    'tvc_p'    => $product->getFinalPrice(),
                    'tvc_url'  => $product->getProductUrl(),
                    'tvc_list' =>  "up_sell_products",
                    'tvc_c' => $tvc_category,
                    'tvc_ss' => $stock
                ];
                array_push($upProducts, $productRel);
            }
        }
        return $upProducts;
    }

    public function getCrossSellProducts(){
        $crossProducts = [];
        $currentProduct = $this->getCurrentProduct();
        $crossSellProducts = $currentProduct->getUpSellProducts();
        if (!empty($crossSellProducts)) {
            foreach ($crossSellProducts as $crossSellProduct) {
                $product = $this->productRepository->get($crossSellProduct->getSku());
                if ($product->isInStock() == 1) {
                        $stock = "in_stock";
                    } else {
                        $stock = "out_of_stock";
                    }
                $tvc_cat_ids = $product->getCategoryIds();
                $tvc_category = $this->getCategoryByIds($tvc_cat_ids);
                $productRel = [
                    'tvc_id' => $product->getId(),
                    'tvc_i'    => $product->getSku(),
                    'tvc_name' => $product->getName(),
                    'tvc_p'    => $product->getFinalPrice(),
                    'tvc_url'  => $product->getProductUrl(),
                    'tvc_list' =>  "cross_sell_products",
                    'tvc_c' => $tvc_category,
                    'tvc_ss' => $stock
                ];
                array_push($crossProducts, $productRel);
            }
        }
        return $crossProducts;
    }

    public function getCartpageInfo()
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $items = $this->cart->getQuote()->getAllVisibleItems();

        $tvc_cart = [];
        foreach ($items as $product) {
            $productUrl = $product->getProductUrl(); //$this->getProductById($product->getId());
            $tvc_cat_ids = $product->getProduct()->getCategoryIds();
            $tvc_category = $this->getCategoryByIds($tvc_cat_ids);

            $var = $this->getSelectedOptions($product);
            $ptionValues = $this->getOptionValues($var);

            $prod_arr = [
                'tvc_id' => $product->getId(),
                'tvc_i'    => $product->getSku(),
                'tvc_name' => $product->getName(),
                'tvc_p'    => $product->getPrice(),
                'tvc_url'  => $productUrl,
                'tvc_q'    => $product->getQty(),
                'tvc_var'  => implode(',', $ptionValues),
                'tvc_c'    => $tvc_category
            ];
            array_push($tvc_cart, $prod_arr);
        }
        return $tvc_cart;
    }

    public function getOptionValues($options){
         $result = [];
            foreach ($options as $key => $value) {
                $result [] = $value['value'];
            }

            return $result;
    }

    public function getSelectedOptions($item){
     $result = [];
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    public function getProductById($pid)
    {
        $product = $this->productRepository->getById($pid);
        return $product->getProductUrl();
    }

    public function getOrderDetails()
    {
        $tvc_order = $this->getTotalOrder();
        $tvc_order_obj = [];
        foreach ($tvc_order->getAllVisibleItems() as $item) {
            $tvc_cat_ids = $item->getProduct()->getCategoryIds();
            $tvc_category = $this->getCategoryByIds($tvc_cat_ids);

            $var = $item->getProductOptions();
            $result = [];
            if ($var) {
                if (isset($var['additional_options'])) {
                    $result = array_merge($result, $var['additional_options']);
                }
                if (isset($var['attributes_info'])) {
                    $result = array_merge($result, $var['attributes_info']);
                }
            }
        
            $ptionValues = $this->getOptionValues($result);

            $prod_arr = [
                'tvc_id'   => $item->getId(),
                'tvc_i'    => $item->getSku(),
                'tvc_name' => $item->getName(),
                'tvc_p'    => $item->getBasePrice(),
                'tvc_Qty'  => $item->getQtyOrdered(),
                'tvc_var'  => implode(',', $ptionValues),
                'tvc_c'    => $tvc_category
            ];

            array_push($tvc_order_obj, $prod_arr);
        }
        return $tvc_order_obj;
    }

    public function getTotalOrder()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        $tvc_order = $this->orderRepository->get($lastOrderId);
        return $tvc_order;
    }

    public function getFbTotal()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart_total = $this->cart->getQuote()->getGrandTotal();
        $cart_item_count = $this->cart->getQuote()->getItemsCount();
        return [$cart_total, $cart_item_count];
    }

    public function getTransactionDetails()
    {
        $tvc_order = $this->getTotalOrder();
        $payment_method = $this->checkoutSession->getLastRealOrder()->getPayment()->getMethod();
        $billingAddress = $tvc_order->getBillingAddress();
        return [
            'tvc_id'             => $this->checkoutSession->getLastRealOrder()->getEntityId(),
            'tvc_revenue'        => $tvc_order->getGrandTotal(),
            'tvc_affiliate'      => $this->getAffiliationName(),
            'tvc_tt'             => $tvc_order->getTaxAmount(),
            'tvc_ts'             => $tvc_order->getShippingAmount(),
            'tvc_payment'        => $payment_method,
            'tvc_billing_country'=> $billingAddress->getCountryId(),
            'tvc_billing_city'   => $billingAddress->getCity(),
            'tvc_dc'             => $tvc_order->getCouponCode()
        ];
    }

    public function getAffiliationName()
    {
        return $this->storeManager->getWebsite()->getName() . ' - ' .
            $this->storeManager->getGroup()->getName() . ' - ' .
            $this->storeManager->getStore()->getName();
    }
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }
    public function getUser_ID()
    {
        if ($this->checkUserId_Enabled()) {
            return $this->customerSession->getCustomer()->getId();
        }
    }
    public function check_IsLoggedIn()
    {
        if ($this->customerSession->getCustomer()->getId() != '') {
            return "Register User";
        } else {
            return "Guest User";
        }
    }

    public function getSearchProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchTerm = $this->queryFactory->get()->getQueryText();

        $filter_1 = $objectManager
            ->create('Magento\Framework\Api\FilterBuilder')
            ->setField('name')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();

        $filter_2 = $objectManager
            ->create('Magento\Framework\Api\FilterBuilder')
            ->setField('description')
            ->setValue('%' . $searchTerm . '%')
            ->setConditionType('like')
            ->create();

            //add our filter(s) to a group
        $filter_group = $objectManager
            ->create('Magento\Framework\Api\Search\FilterGroupBuilder')
            ->create();

        $filter_group->setData('filters', [$filter_1,$filter_2]);  

        $search_criteria = $objectManager
            ->create('Magento\Framework\Api\SearchCriteriaBuilder')
            ->setFilterGroups([$filter_group])
            ->create();
        
        $tvc_prod_obj = [];
        $parentId = [];
        $productdata = $objectManager->create('\Magento\Catalog\Model\ProductRepository')->getList($search_criteria);
        $productIds = array();
    
        foreach ($productdata->getItems() as $product) {
            $productIds[] = $this->getParentProducts($product->getId());
        }

        foreach ($productIds as $key => $value) {
            foreach ($value as $keys => $values) {
                $parentId[] = $values;
            }
        }
        $parentId = array_unique($parentId);

        $collection = $this->collectionFactory->create()->addIdFilter($parentId);
        $collection->addAttributeToSelect('*')
        ->addAttributeToSort('sort_order','ASC')
        ->setCurPage($this->_toolbar->getCurrentPage())
        ->setPageSize($this->setPageLimit());
        
        foreach ($collection as $product) {
           
            $tvc_cat_ids = $product->getCategoryIds();
            $tvc_category = $this->getCategoryByIds($tvc_cat_ids);
            if ($product->isInStock() == true) {
                $stock = "in_stock";
            } else {
                $stock = "out_of_stock";
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $currency = $objectManager->get('Magento\Directory\Model\Currency');

            $prod_arr = [
                'tvc_id'   => $product->getId(),
                'tvc_i'    => $product->getSku(),
                'tvc_name' => $product->getName(),
                'tvc_p'    => $currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                'tvc_c'    => $tvc_category, 
                'tvc_ss'   => $stock,
                'tvc_url'  => $product->getProductUrl()
            ];
            array_push($tvc_prod_obj, $prod_arr);
        }
        return $tvc_prod_obj;
    }

    public function getActualProductUrl($productId)
    {
        $product = $this->_productRepos->getById($productId);
        return $product->getUrlModel()->getUrl($product);
    }

     /**
     * Get website identifier
     *
     * @return string|int|null
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    public function getParentProducts($childId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getParentIdsByChild($childId);
    }

    public function getBestSellerProducts()
    {
        $tvc_bestSellerProd = [];
        $productIds = [];
        $bestSellers = $this->_bestSellersCollectionFactory->create()
            ->setPeriod($this->getBestSellerPeriod());
        foreach ($bestSellers as $product) {
            $productIds[] = $this->getParentProducts($product->getProductId());
        }

        foreach ($productIds as $key => $value) {
            foreach ($value as $key => $values) {
                $parentId[] = $values;
            }
        }
        $parentId = array_unique($parentId);
        $collection = $this->collectionFactory->create()->addIdFilter($parentId);
        $collection->addMinimalPrice()
		    ->addFinalPrice()
		    ->addTaxPercents()
		    ->addAttributeToSelect('*')
            ->setPageSize($this->setPageLimit());

        foreach ($collection as $product) {
            if ($product->isInStock() == true) {
                $stock = "in_stock";
            } else {
                $stock = "out_of_stock";
            }
            $prod_arr = [
                'tvc_id'   => $product->getId(),
                'tvc_i'    => $product->getSku(),
                'tvc_name' => $product->getName(),
                'tvc_p'    => $product->getFinalPrice(),
                'tvc_list' =>  "Home Page",
                'tvc_ss' => $stock,
                'tvc_st' => $product->getQty(),
                'tvc_url'  => $product->getProductUrl()
            ];
            array_push($tvc_bestSellerProd, $prod_arr);
        }
        return $tvc_bestSellerProd;
    }
    

    public function getHpCatProducts()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tvc_hpCatProd = [];
        $cat_ids = $this->getHpCategoryID();
        $cat_ids = array($cat_ids);
        $currentOrder = $this->_toolbar->getCurrentOrder();
        $currentDirection = $this->_toolbar->getCurrentDirection();

        if (!empty($cat_ids)) {
            $tvc_category   = $this->categoryFactory->create()->load($cat_ids);
            $tvc_collection = $this->tvc_categoryCollectionFactory->create();
            $tvc_collection->addAttributeToSelect('*');
            $tvc_collection->addCategoryFilter($tvc_category);
            $tvc_collection->getSelect()->reset(\Zend_Db_Select::ORDER);
            $tvc_collection->setOrder($currentOrder, $currentDirection);
            $tvc_collection->setPageSize($this->setPageLimit());
            $tvc_collection->setCurPage($this->_toolbar->getCurrentPage());

            $tvc_collection->distinct(true);
            
            $tvc_category = $this->getCategoryByIds($cat_ids);

            foreach ($tvc_collection as $product) {
                if ($product->isInStock() == true) {
                    $stock = "in_stock";
                } else {
                    $stock = "out_of_stock";
                }

                $prod_arr = [
                    'tvc_id'   => $product->getId(),
                    'tvc_i'    => $product->getSku(),
                    'tvc_name' => $product->getName(),
                    'tvc_p'    => $product->getFinalPrice(),
                    'tvc_list' =>  "Home Page",
                    'tvc_cat'  => $tvc_category,
                    'tvc_ss' => $stock,
                    'tvc_st' => $product->getQty(),
                    'tvc_url'  => $product->getProductUrl()
                ];
                array_push($tvc_hpCatProd, $prod_arr);
            }
            return $tvc_hpCatProd;
        }
    }

    public function getProductsByManufacturer()
    {
        $manufacturerLabel = $this->getHpBrandCode();
        $brand = explode(",", $manufacturerLabel);

        if (!empty($manufacturerLabel)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productcollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            $brandProductCollection = $productcollection->addAttributeToSelect('*')->addAttributeToFilter('manufacturer', ['in' => $brand]);
            // here filter with manufacturer value instead of label
            $brandProductCollection->addAttributeToSort($this->_request->getParam('product_list_order'));
            $brandProductCollection->setOrder('position');
            $brandProductCollection->setCurPage($this->_toolbar->getCurrentPage());
            $brandProductCollection->setPageSize($this->setPageLimit());
            $allProd = [];
            foreach ($brandProductCollection as $brandProd) {
                $prodArr = [
                    'tvc_id' => $brandProd->getId(),
                    'tvc_i' => $brandProd->getSku(),
                    'tvc_name' => $brandProd->getName(),
                    'tvc_b' => $brandProd->getBrand(),
                    'tvc_p'  => $brandProd->getPrice(),
                    'tvc_url' => $brandProd->getProductUrl()
                ];

                array_push($allProd, $prodArr);
            }
            return $allProd;
        }
    }
}
