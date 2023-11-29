<?php
namespace Logicrays\Customization\Plugin;

class FinalPricePlugin
{
    public function beforeSetTemplate(\Magento\Catalog\Pricing\Render\FinalPriceBox $subject, $template)
    {
        if ($template == 'Magento_Catalog::product/price/final_price.phtml') {
            return ['Logicrays_Customization::product/price/final_price.phtml'];
        } 
        else
        {
            return [$template];
        }
    }
}