<?php

namespace Mageants\GiftCard\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Model\Product;
use Mageants\GiftCard\Model\Customer;
use Mageants\GiftCard\Helper\Data as Helperdata;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Output;
use Magento\Checkout\Model\Cart;

class ViewHelperData implements ArgumentInterface
{
    /**
     * @var Output
     */
    public $output;

    /**
     * @var OrderRepositoryInterface
     */
    public $orderrepository;

    /**
     * @var StoreManagerInterface
     */
    public $storemanager;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var Helperdata
     */
    public $helperdata;

    /**
     * @var Customer
     */
    public $customer;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var Data
     */
    public $data;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var cart
     */
    public $cart;

    /**
     * @param Registry $registry
     * @param Data $data
     * @param OrderRepositoryInterface $orderrepository
     * @param Product $product
     * @param Customer $customer
     * @param Currency $currency
     * @param StoreManagerInterface $storemanager
     * @param Helperdata $helperdata
     * @param Output $output
     * @param Cart $cart
     */
    public function __construct(
        Registry $registry,
        Data $data,
        OrderRepositoryInterface $orderrepository,
        Product $product,
        Customer $customer,
        Currency $currency,
        StoreManagerInterface $storemanager,
        Helperdata $helperdata,
        Output $output,
        Cart $cart
    ) {
        $this->registry = $registry;
        $this->data = $data;
        $this->product = $product;
        $this->customer = $customer;
        $this->helperdata = $helperdata;
        $this->currency = $currency;
        $this->storemanager = $storemanager;
        $this->orderrepository = $orderrepository;
        $this->output = $output;
        $this->cart = $cart;
    }
    /**
     * Get Title
     */
    public function getTitle()
    {
        return 'Hello World';
    }

    /**
     * Get Register
     */
    public function getRegister()
    {
        $register = $this->registry;
        return $register;
    }

    /**
     * Get Output
     */
    public function getHelpoutput()
    {
        $helpoutput = $this->output;
        return $helpoutput;
    }

    /**
     * Get Data
     */
    public function getData()
    {
        $data = $this->data;
        return $data;
    }

    /**
     * Get Order Detail
     */
    public function getOrderId()
    {
        $order = $this->orderrepository;
        return $order;
    }

    /**
     * Get Customer Detail
     */
    public function getCustomerdetail()
    {
        $customer = $this->customer;
        return $customer;
    }

    /**
     * Get Product Detail
     */
    public function getProductdetail()
    {
        $product = $this->product;
        return $product;
    }

    /**
     * Get helper
     */
    public function getHelperdata()
    {
        $helpdata = $this->helperdata;
        return $helpdata;
    }

    /**
     * Get helper
     */
    public function getCurrency()
    {
        $curr = $this->currency;
        return $curr;
    }

    /**
     * Get Store
     */
    public function getStoredetail()
    {
        $store = $this->storemanager;
        return $store;
    }
}
