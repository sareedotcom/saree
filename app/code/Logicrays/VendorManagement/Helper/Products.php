<?php

namespace Logicrays\VendorManagement\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param UrlInterface $backendUrl
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        UrlInterface $backendUrl,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
    }

    /**
     * Get products tab Url in admin
     *
     * @return string
     */
    public function getProductsGridUrl()
    {
        return $this->_backendUrl->getUrl('vendor/manage/products', ['_current' => true]);
    }
}
