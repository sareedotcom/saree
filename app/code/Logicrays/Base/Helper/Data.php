<?php
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_Base
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

declare(strict_types=1);

namespace Logicrays\Base\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const MODULE_CONFIG_PATH = 'logicrays/';

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @type ObjectManagerInterface
     */
    protected $objectManager;

     /**
     * @type urlInterface
     */
    protected $urlInterface;

    /**
     * @type productMetadata
     */
    protected $productMetadata;


     /**
     * Helper Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param ProductMetadataInterface $productMetadata
     *
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->productMetadata = $productMetadata;

        parent::__construct($context);
    }

    /**
     * Check module is enabled or not
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->getGeneralConfig('enabled', $storeId);
    }

    /**
     * Get module general store configuration
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(static::MODULE_CONFIG_PATH . 'general/' . $code, $storeId);
    }

    /**
     * Get store configuration value
     *
     * @param $field
     * @param int $storeid
     *
     * @return array|mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Will return current url
     *
     * @return mixed
     */
    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    /**
     * Will prepare and return url
     *
     * @return mixed
     */
    public function getUrl($value)
    {
        return $this->urlInterface->getUrl($value);
    }

    /**
     * Will return base url
     *
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->urlInterface->getBaseUrl();
    }

    /**
     * Will return current magento version
     *
     * @return string
     */
    public function getMagentoVersion() {
        return $this->productMetadata->getVersion();
    }

    /**
     * Will check current magento version and passed version is greter or equal
     *
     * @param $version
     * @param string $operator
     *
     * @return mixed
     */
    public function compareVersion($version, $operator = '>=')
    {
        $currentmagentoVer = $this->getMagentoVersion();
        return version_compare($currentmagentoVer, $version, $operator);
    }
}
