<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingCost
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingCost\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\GeoIP\Helper\Address;

/**
 * Class Data
 * @package Mageplaza\ShippingCost\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH  = 'mpshippingcost';
    const CONFIG_PATH_DEFAULT = 'default_values';

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Allregion
     */
    private $allRegion;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Country $country
     * @param Allregion $allRegion
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        Country $country,
        Allregion $allRegion,
        ProductRepositoryInterface $productRepository,
        Cart $cart
    ) {
        $this->priceCurrency     = $priceCurrency;
        $this->country           = $country;
        $this->allRegion         = $allRegion;
        $this->productRepository = $productRepository;
        $this->cart              = $cart;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();
        if ($websiteId = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            /** @var Website $website */
            $website = $this->storeManager->getWebsite($websiteId);
            $scope   = $website->getDefaultStore()->getId();
        }

        return $scope;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return $this->getConfigGeneral('title', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getDescription($storeId = null)
    {
        return $this->getConfigGeneral('description', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getNotFoundMsg($storeId = null)
    {
        return $this->getConfigGeneral('not_found_message', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getCountry($storeId = null)
    {
        $value = $this->getConfigGeneral('country', $storeId);

        return $value ? explode(',', $value) : [];
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getPosition($storeId = null)
    {
        $value = $this->getConfigGeneral('position', $storeId);

        return $value ? explode(',', $value) : [];
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getFields($storeId = null)
    {
        $value = $this->getConfigGeneral('fields', $storeId);

        if (!$value) {
            return [];
        }

        $fields = explode(',', $value);

        if (in_array('region', $fields, true)) {
            $fields[] = 'regionId';
        }

        return $fields;
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function getPopup($storeId = null)
    {
        return (bool) $this->getConfigGeneral('popup', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getCondition($storeId = null)
    {
        return $this->getConfigGeneral('condition', $storeId);
    }

    /********************************** Default Configuration *********************
     *
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getDefaultValuesConfig($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_PATH_DEFAULT . '/' . $code : self::CONFIG_PATH_DEFAULT;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->getDefaultValuesConfig('country', $store);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getDefaultRegion($store = null)
    {
        return $this->getDefaultValuesConfig('region_text', $store);
    }

    /**
     * @param null $store
     *
     * @return int
     */
    public function getDefaultRegionId($store = null)
    {
        return (int) $this->getDefaultValuesConfig('region_select', $store);
    }

    /**
     * @param null $store
     *
     * @return string
     */
    public function getDefaultPostcode($store = null)
    {
        return $this->getDefaultValuesConfig('postcode', $store);
    }

    /**
     * @param float $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     * @param Currency|null $currency
     *
     * @return float|string
     */
    public function convertPrice($amount, $format = true, $includeContainer = true, $scope = null, $currency = null)
    {
        return $format ? $this->priceCurrency->convertAndFormat(
            $amount,
            $includeContainer,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $scope,
            $currency
        ) : $this->priceCurrency->convert($amount, $scope, $currency);
    }

    /**
     * @param bool $isMultiselect
     * @param string $foregroundCountries
     *
     * @return array
     */
    public function getCountries($isMultiselect = false, $foregroundCountries = '')
    {
        return $this->country->toOptionArray($isMultiselect, $foregroundCountries);
    }

    /**
     * @param bool $isMultiselect
     *
     * @return array
     */
    public function getAllRegions($isMultiselect = false)
    {
        return $this->allRegion->toOptionArray($isMultiselect);
    }

    /**
     * @param array $data
     *
     * @return false|ProductInterface|Product
     */
    public function initProduct($data)
    {
        if (empty($data['product'])) {
            return false;
        }

        try {
            $storeId = $this->storeManager->getStore()->getId();

            return $this->productRepository->getById($data['product'], false, $storeId);
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e);

            return false;
        }
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    public function addProduct($data)
    {
        if (!$product = $this->initProduct($data)) {
            return;
        }

        $quote = $this->cart->getQuote();

        if (!$quote->getId()) {
            $this->cart->save();
        }

        if (empty($data['include_cart'])) {
            $quote->removeAllItems();
        }

        $this->cart->addProduct($product, $data);

        if (!empty($data['related_product'])) {
            $this->cart->addProductsByIds(explode(',', $data['related_product']));
        }

        $address = new DataObject(self::jsonDecode($data['address']));
        $quote->getShippingAddress()
            ->setCountryId($address->getData('country'))
            ->setRegionId($address->getData('region_id'))
            ->setPostcode($address->getData('postcode'))
            ->setRegion($address->getData('region'))
            ->setId(null)
            ->setCollectShippingRates(true);
        $quote->collectTotals();
    }

    /**
     * @return Address
     */
    public function getGeoIpHelper()
    {
        return $this->getObject(Address::class);
    }

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getGeoIpData($storeId = null)
    {
        if ($this->isModuleOutputEnabled('Mageplaza_GeoIP')) {
            return $this->getGeoIpHelper()->getGeoIpData($storeId);
        }

        return [];
    }
}
