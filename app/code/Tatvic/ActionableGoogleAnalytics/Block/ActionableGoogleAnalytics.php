<?php

namespace Tatvic\ActionableGoogleAnalytics\Block;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Tatvic\ActionableGoogleAnalytics\Helper\Data;

/**
 * Class EnhancedEcommerce
 * @package Tatvic\ActionableGoogleAnalytics\Block
 */
class ActionableGoogleAnalytics extends Template
{
    /**
     * @var Data
     */
    public $_helper;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var
     */
    protected $blockFactory;
    /**
     * @var Logo
     */
    protected $_logo;
    /**
     * @var
     */
    protected $productCollectionFactory;
    /**
     * @var Visibility
     */
    protected $productVisibility;
    /**
     * @var Status
     */
    protected $productStatus;
    /**
     * @var CategoryFactory
     */
    private $_categoryFactory;
    /**
     * @var CollectionFactory
     */
    private $_productCollectionFactory;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $_currency;

    /**
     * ActionableGoogleAnalytics constructor.
     * @param Template\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param Http $request
     * @param Data $helper
     * @param Logo $logo
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        Http $request,
        Data $helper,
        Logo $logo,
        Status $productStatus,
        Visibility $productVisibility,
        StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_logo = $logo;
        $this->_helper = $helper;
        $this->request = $request;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;


        parent::__construct($context, $data);
    }

    public function isEnabled(){
        return $this->_helper->isEnabled();
    }

    /**
     * @return mixed
     */
    public function mode()
    {
        return $this->_helper->mode();
    }
    public function method()
    {
        return $this->_helper->method();
    }
    public function getMeasurmentIDGA4()
    {
        return $this->_helper->getMeasurmentIDGA4();
    }
    public function getUaIDBoth()
    {
        return $this->_helper->getUaIDBoth();
    }
    public function getMeasurmentIdBoth()
    {
        return $this->_helper->getMeasurmentIdBoth();
    }
    public function getUaId()
    {
        return $this->_helper->getUaID();
    }
    public function checkDF_enabled()
    {
        return $this->_helper->checkDf_enabled();
    }
    public function getGtm()
    {
        return $this->_helper->getGtm();
    }
    public function checkIP_anonymization()
    {
        return $this->_helper->checkIP_enabled();
    }
    public function getLinkAttribution()
    {
        return $this->_helper->getLinkAttribution();
    }
    public function getDisableAdsFeature()
    {
        return $this->_helper->getDisableAdsFeature();
    }
    public function checkClientId_Enabled()
    {
        return $this->_helper->checkClientId_Enabled();
    }
    public function checkOptOut_Enabled()
    {
        return $this->_helper->checkOptOut_Enabled();
    }

    public function getBestSeller(){
        return $this->_helper->getBestSeller();
    }
    public function FBPixelEnabled()
    {
        return $this->_helper->checkFbpixelEnabled();
    }
    public function AdwordsEnabled()
    {
        return $this->_helper->checkAdwords_Enabled();
    }
    public function getAdwordsID()
    {
        return $this->_helper->getAdwordsID();
    }
    public function getAdwordsLabel()
    {
        return $this->_helper->getAdwordsLabel();
    }

    public function getOptimizeIP()
    {
        return $this->_helper->getOptimizeIP();
    }
    public function getFbPixelID()
    {
        return $this->_helper->getFBPixelID();
    }
    public function getMagentoVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        return $version;
    }

    public function getAction()
    {
        $get_action = $this->request->getFullActionName();
        if (method_exists($this, $get_action)) {
            $data = $this->$get_action();
            return [$data, $get_action];
        }else{
            return $get_action;
        }
    }
    
    public function getCheckoutActionName(){
        return $this->request->getFullActionName();
    }

    public function getCustomAction(){
        return $this->request->getFullActionName();
    }
    public function getLocalCurrency()
    {
        return $this->_helper->getCurrencyCode();
    }
    public function get_user_ID()
    {
        $user_id = '';
        if ($this->_helper->getUser_ID()) {
            $user_id = $this->_helper->getUser_ID();
        }
        return base64_encode($user_id);
    }
    
    public function getTotalOrder()
    {
        return $this->_helper->getTotalOrder();
    }

    public function getTotal()
    {
        return $this->_helper->getFbTotal();
    }
    
    public function isAdminLogin(){
    	return $this->_helper->isAdminLogin();
    }

    protected function catalogsearch_result_index()
    {
        $searchProducts = $this->_helper->getSearchProducts();
        return json_encode($searchProducts);
    }

    protected function catalog_category_view()
    {
        $t_getCategoryProduct = $this->_helper->getCategoryProduct();
        return json_encode($t_getCategoryProduct);
    }

    protected function catalog_product_view()
    {
        $tvc_product_details = $this->_helper->getProductDetails();
        $tvc_related_products = $this->_helper->getRalatedProducts();
        $tvc_upsell_products = $this->_helper->getUpSellProducts();
       // $tvc_crosssell_products = $this->_helper->getCrossSellProducts();
        array_push($tvc_product_details, $tvc_related_products,$tvc_upsell_products);
        return json_encode($tvc_product_details);
    }

    protected function checkout_cart_index()
    {
        $tvc_cart_items = $this->_helper->getCartpageInfo();
        return json_encode($tvc_cart_items);
    }

    public function checkout_index_index()
    {
        $tvc_cart_items = $this->_helper->getCartpageInfo();
        return json_encode($tvc_cart_items);
    }

    protected function checkout_onepage_success()
    {
        $tvc_order_obj = $this->_helper->getOrderDetails();
        $tvc_trans_detail = $this->_helper->getTransactionDetails();
        array_push($tvc_order_obj, $tvc_trans_detail);
        return json_encode($tvc_order_obj);
    }

    public function getHomePageCatProd(){
        $tvc_hp_products = $this->_helper->getHpCatProducts();
        return json_encode($tvc_hp_products);
    }

    public function getProductsByManufacturer   (){
        $tvc_hp_brand_prod = $this->_helper->getProductsByManufacturer();
        return json_encode($tvc_hp_brand_prod);
    }

    public function getBestSellerProducts(){
        $tvc_best_seller = $this->_helper->getBestSellerProducts();
        return json_encode($tvc_best_seller);
    }

    public function formFieldTracking_Enable(){
        return $this->_helper->formFieldTracking_Enable();
    }

    public function isCustomerLoggedIn()
    {
        return $this->_helper->check_IsLoggedIn();
    }

    /**
     * Check if current url is url for home page
     */
    public function isHomePage()
    {
        return $this->_logo->isHomePage();
    }
    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
}
