<?php

namespace Elsnertech\CustomRelatedproduct\Block;

class Product extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_productRepository;
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {        
        $this->_registry = $registry;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCurrentProduct()
    {        
        $_product = $this->_registry->registry('current_product')->getCustomRelatedProducts();
        // $_attributeValue = $_product->getProduct()->getData('custom_related_products');
        return $_product;
    }
    public function getProductBySku($sku)
	{
		return $this->_productRepository->get($sku);
	}
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    public function getCurrentProductBookAppoinment()
    {        
        $_product = $this->_registry->registry('current_product')->getIsBookAppoinment();
        return $_product;
    }
}
?>