<?php
namespace Elsner\Discount\Observer;

class Productsaveafter implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
  	 $product = $observer->getProduct();
  	 $pro_price = $product->getPrice();
     $pro_price = floatval(str_replace(",","",$pro_price));
  	 $pro_sprcialprice = $product->getSpecialPrice();
     $pro_sprcialprice = floatval(str_replace(",","",$pro_sprcialprice));
  	 if(!empty($pro_sprcialprice)){
      $newprice = (($pro_price - $pro_sprcialprice) * 100) / $pro_price;
  	 	$product->setMjDiscount($newprice);
  	 }
     return $this;
  }
}