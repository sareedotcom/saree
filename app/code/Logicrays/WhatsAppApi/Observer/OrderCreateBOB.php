<?php

namespace Logicrays\WhatsAppApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Logicrays\WhatsAppApi\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\Currency;

class OrderCreateBOB implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * BusinessOnBot is enable or not config path
     */
    const BUSINESS_ON_BOT_IS_ENABLE_KEY = "businessonbot/businessonbot/enable";
    
    public function __construct(
        JsonHelper $jsonHelper,
        Curl $curl,
        Data $helperData,
        StoreManagerInterface $storeManager,
        Currency $currencyModel
    ) {
        $this->_curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->currencyModel = $currencyModel;
    }

    /**
     * Execute
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        if($this->helperData->getConfigValue(Self::BUSINESS_ON_BOT_IS_ENABLE_KEY)){
            
            $url = 'https://customstore.getbob.link/saree/orders-create';
            $order = $observer->getEvent()->getOrder();

            $shippingAddress = $order->getShippingAddress();
            $street = $shippingAddress->getStreet();
            $street1 = $street[0];
            $street2 = "";
            if(isset($street[1])){
                $street2 = $street[1];
            }
            $allItems = $order->getAllItems();
            $lineItems = [];
            $store = $this->storeManager->getStore();
            foreach ($allItems as $item) {
                $singelItem = [];
                $img['originalSrc'] = $store->getBaseUrl() . 'media/catalog/product' . $item->getProduct()->getThumbnail();
                $product['id'] = $item->getProductId();
                $product['title'] = $item->getName();
                $singelItem['image'] = $img;
                $singelItem['product'] = $product;
                $singelItem['quantity'] = $item->getQtyOrdered();
                $singelItem["variant"]['id'] =  "40344786960540";
                $singelItem["variant"]['title'] =  "Default Title";
                $singelItem["variant"]['price'] =  "100.00";
                $singelItem["variant"]['weight'] =  "1 kg";
                $singelItem["variant"]['sku'] =  $item->getSku();
                $singelItem["variantTitle"] = "test";
                $lineItems[] = $singelItem;
            }
            $currencySymbol = $this->currencyModel->load($order->getOrderCurrencyCode())->getCurrencySymbol();
            $requestPayload = [
                "id" => $order->getIncrementId(),
                "name" => $order->getIncrementId(),
                "email" => $order->getCustomerEmail(),
                "createdAt" => date('Y/m/d'),
                "fullyPaid" => false,
                "cancelReason" => null,
                "cancelledAt" => null,
                "note" => "",
                "channel" => "Magento",
                "shippingAddress" => [
                    "name" => $shippingAddress->getFirstname(),
                    "phone" => $shippingAddress->getTelephone(),
                    "address1" => $street1,
                    "address2" => $street2,
                    "city" => $shippingAddress->getCity(),
                    "province" => $shippingAddress->getRegion(),
                    "country" => $shippingAddress->getCountryId(),
                    "zip" => $shippingAddress->getPostCode()
                ],
                "total_amount" => $order->getGrandTotal(),
                "currencyCode" => $currencySymbol,
                "shipment_details" => [
                    "status" => "Pending",
                    "tracking_info" => $order->getTrackingNumbers()
                ]
            ];
            $requestPayload["lineItems"][] = $singelItem;

            $this->helperData->businessOnBotCurl('POST',$url,$requestPayload);
        }
        // "lineItems" => [
        //     [
        //         "image" => [
        //             "originalSrc" => "Expecting ImageURL"
        //         ],
        //         "product" => [
        //             "id" => "MNEE1493",
        //             "title" => "Pink Banarasi Jacquard Indo Western Outfit"
        //         ],
        //         "variant" => [
        //             "id" => "40344786960540",
        //             "title" => "Default Title",
        //             "price" => "100.00",
        //             "weight" => "1 kg",
        //             "sku" => "test"
        //         ],
        //         "variantTitle" => "test",
        //         "quantity" => 2
        //     ]
        // ],
    }
}