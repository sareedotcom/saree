<?php

namespace Logicrays\WhatsAppApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Logicrays\WhatsAppApi\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class ChangeStatusForBOB implements ObserverInterface
{
     /**
     * BusinessOnBot is enable or not config path
     */
    const BUSINESS_ON_BOT_IS_ENABLE_KEY = "businessonbot/businessonbot/enable";

    protected $order;

    public function __construct(
        Order $order,
        Data $helperData,
        StoreManagerInterface $storeManager,
        CollectionFactory $orderCollectionFactory
    )
    {
        $this->order = $order;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute(Observer $observer)
    {

        if($this->helperData->getConfigValue(Self::BUSINESS_ON_BOT_IS_ENABLE_KEY)){

            $order = $observer->getEvent()->getOrder();
            $customerEmail = $order->getCustomerEmail();
            $billingAddress = $order->getBillingAddress();
            $store = $this->storeManager->getStore();
            if($order->getStatus() == "complete"){
                $allItems = $order->getAllItems();
                $totalSpent=0;
            
                $customerOrder = $this->_orderCollectionFactory->create()->addAttributeToFilter('customer_email', $customerEmail)->addAttributeToSort('created_at', 'ASC')->load();
                foreach($customerOrder AS $customerOrd){
                    $lastOrderId = $customerOrd->getIncrementId();
                    $totalSpent = $totalSpent + $customerOrd->getGrandTotal();
                }
                foreach ($allItems as $item) {
                    $singelItem = [];
                    $img['originalSrc'] = $store->getBaseUrl() . 'media/catalog/product' . $item->getProduct()->getThumbnail();
                    $product['id'] = $item->getProductId();
                    $product['title'] = $item->getName();
                    $singelItem['image'] = $img;
                    $singelItem['product'] = $product;
                    $singelItem['quantity'] = $item->getQtyOrdered();
                    $singelItem["variant"]['id'] =  $item->getSku();
                    $singelItem["variant"]['title'] =  $item->getName();
                    $singelItem["variant"]['price'] =  $item->getPrice();
                    $singelItem["variant"]['weight'] =  "1 kg";
                    $singelItem["variant"]['sku'] =  $item->getSku();
                    $singelItem["variantTitle"] = $item->getName();
                    $lineItems[] = $singelItem;
                }
                $requestPayload = [
                    "fulfillment_id" => $order->getIncrementId(),
                    "id" => $order->getIncrementId(),
                    "id_alias" => $order->getIncrementId(),
                    "customer" => [
                        "email" => $order->getCustomerEmail(),
                        "first_name" => $order->getCustomerFirstname(),
                        "last_name" => $order->getCustomerLastname(),
                        "orders_count" => $customerOrder->count(),
                        "total_spent" => $totalSpent,
                        "last_order_id" => $lastOrderId,
                        "phone" => $billingAddress->getTelephone()
                    ],
                    "order_details" => [
                        "total_price" => $order->getGrandTotal(),
                        "total_tax" => $order->getTaxAmount(),
                        "total_discount" => $order->getDiscountAmount(),
                        "currency" => $order->getOrderCurrencyCode()
                    ],
                    "tracking_info" => [
                        "tracking_number" => $order->getTrackingNumbers(),
                        "tracking_url" => "Not Available",
                        "tracking_company_name" => "Not Available",
                        "shipping_status" => $order->getStatus()
                    ],
                    "phone" => $billingAddress->getTelephone(),
                    "fulfilled_at" => date('Y-m-d')
                ];
                $requestPayload["lineItems"][] = $singelItem;
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
                $url = "https://customstore.getbob.link/saree/fulfillments-create";
                $this->helperData->businessOnBotCurl('POST',$url,$requestPayload);
            }
            else if($order->getStatus() == "canceled"){
                $allItems = $order->getAllItems();
                $store = $this->storeManager->getStore();
                foreach ($allItems as $item) {
                    $singelItem = [];
                    $img['originalSrc'] = $store->getBaseUrl() . 'media/catalog/product' . $item->getProduct()->getThumbnail();
                    $product['id'] = $item->getProductId();
                    $product['title'] = $item->getName();
                    $singelItem['image'] = $img;
                    $singelItem['product'] = $product;
                    $singelItem['quantity'] = $item->getQtyOrdered();
                    $singelItem["variant"]['id'] =  $item->getSku();
                    $singelItem["variant"]['title'] =  $item->getName();
                    $singelItem["variant"]['price'] =  $item->getPrice();
                    $singelItem["variant"]['weight'] =  "1 kg";
                    $singelItem["variant"]['sku'] =  $item->getSku();
                    $singelItem["variantTitle"] = $item->getName();
                    $lineItems[] = $singelItem;
                }
                $shippingAddress = $order->getBillingAddress();
                $street = $shippingAddress->getStreet();
                $street1 = $street[0];
                $street2 = "";
                if(isset($street[1])){
                    $street2 = $street[1];
                }

                $requestPayload = [
                    "id" => $order->getIncrementId(),
                    "name" => $order->getIncrementId(),
                    "email" => $order->getCustomerEmail(),
                    "createdAt" => date('Y-m-d'),
                    "fullyPaid" => false,
                    "cancelReason" => "cancellation request",
                    "cancelledAt" => "time in utc",
                    "note" => "",
                    "channel" => "Magento",
                    "shippingAddress" => [
                        "name" => $order->getCustomerFirstname(),
                        "phone" => $shippingAddress->getTelephone(),
                        "address1" => $street1,
                        "address2" => $street2,
                        "city" => $shippingAddress->getCity(),
                        "province" => $shippingAddress->getRegion(),
                        "country" => $shippingAddress->getCountryId(),
                        "zip" => $shippingAddress->getPostCode()
                    ],
                    "total_amount" => $order->getGrandTotal(),
                    "currencyCode" => $order->getOrderCurrencyCode(),
                    "shipment_details" => [
                        "status" => $order->getStatus(),
                        "tracking_info" => "Not Available"
                    ]
                ];
                $requestPayload["lineItems"][] = $singelItem;
                $url = "https://customstore.getbob.link/saree/orders-cancelled";
                $this->helperData->businessOnBotCurl('POST', $url, $requestPayload);
                
            }
        }
    }
}