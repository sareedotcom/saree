<?php
namespace Logicrays\Customization\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\Product;

class Customization extends Template
{
    protected $priceCurrency; 

    /**
     * Construct
     * 
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param CurrencyFactory $currencyFactory
     * @param RuleFactory $ruleFactory
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * 
    */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        CurrencyFactory $currencyFactory,
        RuleFactory $ruleFactory,
        Cart $cart,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        Product $product,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->currencyFactory = $currencyFactory;
        $this->ruleFactory = $ruleFactory;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->product = $product;
        parent::__construct($context, $data);
    }

    /**
     * Get Rounded Price
     * 
     * @param $price
     * 
     * @return void
     */
    public function getRoundedPrice($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * Get Current Currency Symbol
     * 
     * @return void
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }

    /**
     * Get Row Item Discount
     * 
     * @param $price 
     * @param $specialPrice 
     * @param $storeManager
     * 
     * @return string
     */
    public function getRowItemDiscount($price, $specialPrice, $storeManager)
    {
        $discountedAmount = $specialPrice - $price;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $_currencyFactory = $objectManager->create('Magento\Directory\Model\CurrencyFactory');
        $quoteCurrency = $storeManager->getBaseCurrencyCode();
        $rateToBase = $this->currencyFactory->create()->load($quoteCurrency)->getAnyRate($storeManager->getCurrentCurrencyCode());
        $discountedAmount = $discountedAmount * $rateToBase;
        return round($discountedAmount,2);
    }

    /**
     * Get Promo Message
     * 
     * @return string
     */
    public function getPromoMessage(){

        $rules = $this->ruleFactory->create()->getCollection()->addIsActiveFilter()->addFieldToFilter('rule_coupons.code', ['null' => true]);
        $rules->addFieldToFilter('simple_free_shipping', ['in' => [1,2]]);

        $rulesByPercent = $this->ruleFactory->create()->getCollection()->addIsActiveFilter()->addFieldToFilter('rule_coupons.code', ['null' => true]);
        $rulesByPercent->addFieldToFilter('simple_free_shipping', ['eq' => 0]);

        $totalQuantity = $this->cart->getQuote()->getItemsCount();
        $subTotal = $this->cart->getQuote()->getBaseSubtotal();

        $freeShipping = [];
        $byPercentage = [];
        $amountArr = [];

        foreach ($rules as $rule) {
            $ruleData = $rule->getConditionsSerialized();
            if ($ruleData) {
                $ruleDataArray = json_decode($ruleData, true);
                if (isset($ruleDataArray['conditions'])) {
                    foreach ($ruleDataArray['conditions'] as $key => $value) {
                        settype($value['value'], "integer");
                        if($value['value'] > $subTotal){
                            $freeShipping[$rule->getId()] = $value['value'];
                        }
                    }
                }
            }
        }

        foreach ($rulesByPercent as $rule) {
            $ruleData = $rule->getConditionsSerialized();
            if ($ruleData) {
                $ruleDataArray = json_decode($ruleData, true);
                if (isset($ruleDataArray['conditions'])) {

                    foreach ($ruleDataArray['conditions'] as $key => $value) {
                        settype($value['value'], "integer");
                        if($value['value'] > $subTotal){
                            $byPercentage[$rule->getId()] = $value['value'];
                        }
                        else if($value['value'] == 1 && isset($value['conditions'])){
                            foreach ($value['conditions'] as $key1 => $value1) {
                                if(($value1['operator'] == ">=" || $value1['operator'] == "==") && $value1['value'] > $subTotal){
                                    $byPercentage[$rule->getId()] = $value1['value'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $merrageArray = $byPercentage + $freeShipping;
        array_unique($merrageArray);

        if($merrageArray){
            $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
            $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $rateToBase = $this->currencyFactory->create()->load($baseCurrency)->getAnyRate($currentCurrencyCode);

            if(isset($byPercentage[array_search(min($merrageArray), $merrageArray)])){
                $discountedAmount = $byPercentage[array_search(min($merrageArray), $merrageArray)];
                $discountedAmount = $discountedAmount - $subTotal;
                $discountedAmount = ($discountedAmount * $rateToBase);

                $rulesByPercentnew = $this->ruleFactory->create()->getCollection()->addFieldToSelect('discount_amount')->addIsActiveFilter()->addFieldToFilter('rule_coupons.code', ['null' => true]);
                $rulesByPercentnew->addFieldToFilter('rule_id', ['eq' => array_search(min($merrageArray), $merrageArray)]);
                $data = $rulesByPercentnew->getData();
                return 'Spent more <span class="save-price">'.$this->getCurrentCurrencySymbol().round($discountedAmount).'</span> to get Extra <span <span class="save-price">'.round($data[0]['discount_amount']).'%<span> off.';
            }
            else if(isset($freeShipping[array_search(min($merrageArray), $merrageArray)])){
                $discountedAmount = $freeShipping[array_search(min($merrageArray), $merrageArray)];
                $discountedAmount = $discountedAmount - $subTotal;
                $discountedAmount = ($discountedAmount * $rateToBase);
                return 'Spent more '.$this->getCurrentCurrencySymbol().round($discountedAmount).' to get free shipping.';
            }
        }
        return "";
    }

    public function getProductPrices($sku){
        // $product = $this->_productRepository->get($sku);
        $price = [];
        $product_id = $sku; //Product ID
        $_product = $this->product->load($product_id);
         
        $price['currentPrice'] = $_product->getPrice();
        $price['currentSpecialPrice'] = $_product->getSpecialPrice();
        
        return $price;
    }
}