<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Block\Product\View;

use Magento\Catalog\Model\Product;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\Registry;
use \Mageants\GiftCard\Helper\Data;

/**
 * @api
 * @since 100.0.2
 */
class Validity extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Return the product using Registry
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * Product Gift Card Validity
     */
    public function getGiftCardValidityByProduct()
    {
        $product=$this->getProduct();
        $validity = false;
       
        if ($product->getValidity()) {
            $validity = $product->getValidity();
        } else {
            $validity = $this->_helper->getValidity();
        }
        return $validity;
    }
}
