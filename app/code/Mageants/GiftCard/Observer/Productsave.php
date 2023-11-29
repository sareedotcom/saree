<?php

namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsave implements ObserverInterface
{
    /**
     * Save the Product
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();  // you will get product object
        $_sku=$_product->getSku();
    }
}
