<?php

namespace Elsnertech\CatalogList\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_customOption;
    protected $_attributeSetRepository;
    protected $_product;

    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Option $customOption,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_customOption = $customOption;
        $this->_attributeSetRepository = $attributeSetRepository;
        $this->_product = $_productloader;
    }
    public function getCustomOption($product)
    {
            $product_option = $this->_customOption->getProductOptionCollection($product);
            return $product_option;
    }

    public function getMediaUrl($path){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
    }

    public function getConfigValue($path){
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
    }

    public function getProductAttributeSetId($productAttributeSetId){
       
        $product_attributeSetRepository = $this->_attributeSetRepository->get($productAttributeSetId);
        $attributeSetName = $product_attributeSetRepository->getAttributeSetName();
            return $attributeSetName;
    }

    public function getProductId($product_id){
        return $this->_product->create()->load($product_id);
    }
}