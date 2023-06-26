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

namespace Mageplaza\ShippingCost\Controller\Index;

use Magento\Checkout\Model\Cart;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Shipping;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ShippingCost\Helper\Data;

/**
 * Class Calculate
 * @package Mageplaza\ShippingCost\Controller\Index
 */
class Calculate extends Action
{
    /**
     * @var RateRequestFactory
     */
    private $rateRequestFactory;

    /**
     * @var RateCollectorInterfaceFactory
     */
    private $rateCollector;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var FreeShippingInterface
     */
    private $freeShipping;

    /**
     * Calculate constructor.
     *
     * @param Context $context
     * @param RateRequestFactory $rateRequestFactory
     * @param RateCollectorInterfaceFactory $rateCollector
     * @param RateFactory $rateFactory
     * @param JsonFactory $resultJsonFactory
     * @param RegionFactory $regionFactory
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     * @param Cart $cart
     * @param FreeShippingInterface $freeShipping
     */
    public function __construct(
        Context $context,
        RateRequestFactory $rateRequestFactory,
        RateCollectorInterfaceFactory $rateCollector,
        RateFactory $rateFactory,
        JsonFactory $resultJsonFactory,
        RegionFactory $regionFactory,
        StoreManagerInterface $storeManager,
        Data $helper,
        Cart $cart,
        FreeShippingInterface $freeShipping
    ) {
        $this->rateRequestFactory = $rateRequestFactory;
        $this->rateCollector      = $rateCollector;
        $this->rateFactory        = $rateFactory;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->regionFactory      = $regionFactory;
        $this->storeManager       = $storeManager;
        $this->helper             = $helper;
        $this->cart               = $cart;
        $this->freeShipping       = $freeShipping;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $result = $this->resultJsonFactory->create();

        try {
            $request = $this->getRateRequest($params);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $result->setData(['error' => true]);
        }

        $data = [];

        foreach ($this->getRates($request) as $rate) {
            $rateData = $rate->getData();

            $rateData['price'] = $this->helper->convertPrice($rate->getPrice());

            $sortOrder = $this->prepareSortOrder($data, $rate->getPrice());

            $data[$sortOrder] = $rateData;
        }

        ksort($data, SORT_NUMERIC);

        return $result->setData(array_values($data));
    }

    /**
     * @param array $data
     * @param int $sortOrder
     *
     * @return int
     */
    protected function prepareSortOrder($data, $sortOrder)
    {
        if (isset($data[$sortOrder])) {
            return $this->prepareSortOrder($data, $sortOrder + 1);
        }

        return $sortOrder;
    }

    /**
     * @param RateRequest $request
     *
     * @return Rate[]
     */
    public function getRates($request)
    {
        /** @var Shipping $rateCollector */
        $rateCollector = $this->rateCollector->create()->collectRates($request);
        $rateResult    = $rateCollector->getResult();

        $rates = [];

        foreach ($rateResult->getAllRates() as $shippingRate) {
            if ($shippingRate instanceof Method) {
                $rates[] = $this->rateFactory->create()->importShippingRate($shippingRate);
            }
        }

        return $rates;
    }

    /**
     * @param array $data
     *
     * @return RateRequest
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getRateRequest($data)
    {
        $cartData = $this->processCart($data);
        $address  = new DataObject(Data::jsonDecode($data['address']));

        $request = $this->rateRequestFactory->create();

        $quote = $this->cart->getQuote();
        $quote->setItems($cartData['items']);
        $request->setFreeShipping($this->freeShipping->isFreeShipping($quote, $quote->getItems()));

        /** @var Item $item */
        foreach ($quote->getItems() as $item) {
            if ($item->getAddress()->getFreeShipping()) {
                $item->getAddress()->setFreeShipping(true);
            }
        }

        $request->setAllItems($quote->getItems());
        $request->setDestCountryId($address->getData('country'));
        $request->setDestRegionId($address->getData('region_id'));
        $request->setDestRegionCode($this->getRegionCode($address));
        $request->setDestPostcode($address->getData('postcode'));
        $request->setPackageValue($cartData['price']);
        $request->setPackageValueWithDiscount($cartData['price'] - $cartData['discount']);
        $request->setPackageWeight($cartData['weight']);
        $request->setPackageQty($cartData['qty']);
        $request->setBaseSubtotalInclTax($cartData['price'] + $cartData['tax']);
        $request->setFreeMethodWeight($quote->getShippingAddress()->getFreeMethodWeight());

        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $request->setStoreId($store->getId());
        $request->setWebsiteId($store->getWebsiteId());
        $request->setBaseCurrency($store->getBaseCurrency());
        $request->setPackageCurrency($store->getCurrentCurrency());

        return $request;
    }

    /**
     * @param DataObject $address
     *
     * @return string
     */
    private function getRegionCode($address)
    {
        if (empty($address->getData('region_id'))) {
            return $address->getData('region');
        }

        return $this->regionFactory->create()->load($address->getData('region_id'))->getCode();
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    private function processCart($data)
    {
        $result = [
            'items'    => [],
            'price'    => 0,
            'discount' => 0,
            'tax'      => 0,
            'weight'   => 0,
            'qty'      => 0,
        ];

        $this->helper->addProduct($data);

        /** @var Item $item */
        foreach ($this->cart->getItems()->getItems() as $item) {
            if (empty($data['include_cart']) && $item->getId()) {
                continue;
            }

            $result['items'][] = $item;

            if ($item->getParentItem()) {
                continue;
            }

            $qty = $item->getQty();

            $result['price']    += $item->getBasePrice() * $qty;
            $result['discount'] += $item->getBaseDiscountAmount() * $qty;
            $result['tax']      += $item->getBaseTaxAmount() * $qty;
            $result['weight']   += $item->getWeight() * $qty;
            $result['qty']      += $qty;
        }

        return $result;
    }
}
