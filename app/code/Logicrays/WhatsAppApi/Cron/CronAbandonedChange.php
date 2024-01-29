<?php

namespace Logicrays\WhatsAppApi\Cron;

use Logicrays\WhatsAppApi\Helper\Data;

class CronAbandonedChange
{
     /**
     * BusinessOnBot is enable or not config path
     */
    const BUSINESS_ON_BOT_IS_ENABLE_KEY = "businessonbot/businessonbot/enable";

    protected $quotesFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reports\Model\ResourceModel\Quote\Collection $collection,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Address $address,
        \Magento\Directory\Model\Country $country,
        Data $helperData
    ) {
        $this->storeManager = $storeManager;
        $this->collection = $collection;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->address = $address;
        $this->country = $country;
        $this->helperData = $helperData;
    }

    /**
     * Cron execute
     *
     * @return void
     */
    public function execute()
    {
        if($this->helperData->getConfigValue(Self::BUSINESS_ON_BOT_IS_ENABLE_KEY)){
            $url = 'https://customstore.getbob.link/saree/abancart';
            $store = $this->storeManager->getStore();
            $storeId = $store->getId();
            $rows = $this->collection->prepareForAbandonedReport([$storeId])->load();
            $abandonedData = [];
            foreach ($rows->getData() as $abandonedCollectionData) {
                $abandonedData = [];
                $abandonedData['checkout_id'] = $abandonedCollectionData['entity_id'];
                $abandonedData['cart_recovery_url'] = $store->getBaseUrl().'checkout/cart';
                $customerId = $abandonedCollectionData['customer_id'];
                $quote = $this->cartRepositoryInterface->getForCustomer($customerId, [$storeId]);
                $items = $quote->getAllVisibleItems();
                foreach ($items as $item) {
                    $tax[] = $item->getTaxAmount();
                    $totalTax = array_sum($tax);
                    $discount[] = $item->getDiscountAmount();
                    $totalDiscount = array_sum($discount);
                    $abandonedData['line_items'][] = [
                        'id' => $item->getProductId(),
                        'name' => $item->getName(),
                        $line_items = 'image' => [
                            'originalSrc' => $store->getBaseUrl() . 'media/catalog/product' . $item->getProduct()->getThumbnail(),
                        ],
                        'quantity' => $item->getQty(),
                        'price' => $item->getPrice(),
                    ];
                }
                $customer = $this->customerFactory->create()->load($customerId);

                $billingAddressId = $customer->getDefaultBilling();
                $billingAddress = $this->address->load($billingAddressId);

                if(!$billingAddress->getTelephone() && !$customer->getMobilenumber()){
                    continue;
                }
                else if($customer->getMobilenumber())
                {
                    $mobileNumber = $customer->getMobilenumber();
                }
                else if($billingAddress->getTelephone())
                {
                    $mobileNumber = $billingAddress->getTelephone();
                }

                $abandonedData['customer'] = [
                    'email' => $customer->getEmail(),
                    'first_name' => $customer->getFirstname(),
                    'last_name' => $customer->getLastname(),
                    'phone' => $mobileNumber,
                ];
                $abandonedData['order_details'] = [
                    'total_price' => $abandonedCollectionData['grand_total'],
                    'total_tax' => $totalTax,
                    'total_discount' => $totalDiscount,
                    'currency' => $abandonedCollectionData['base_currency_code'],
                ];
                
                $billingStreet = $billingAddress->getStreet();
                $billingStreetAddress = "";
                if (!empty($billingStreet[0])) {
                    $billingStreetAddress = $billingStreet[0];
                }
                if (!empty($billingStreet[1])) {
                    $billingStreetAddress = $billingStreetAddress.', '.$billingStreet[1];
                }
                if (!empty($billingStreet[2])) {
                    $billingStreetAddress = $billingStreetAddress.', '.$billingStreet[2];
                }

                if(!$billingStreetAddress){
                    continue;
                }

                $billingCountrycode = $billingAddress->getCountryId();
                $billingCountry = $this->country->load($billingCountrycode)->getName();

                $shippingAddressId = $customer->getDefaultShipping();
                $shippingAddress = $this->address->load($shippingAddressId);

                $shippingStreet = $shippingAddress->getStreet();
                $shippingStreetAddress = "";
                if (!empty($shippingStreet[0])) {
                    $shippingStreetAddress = $shippingStreet[0];
                }
                if (!empty($shippingStreet[1])) {
                    $shippingStreetAddress = $shippingStreetAddress.', '.$shippingStreet[1];
                }
                if (!empty($shippingStreet[2])) {
                    $shippingStreetAddress = $shippingStreetAddress.', '.$shippingStreet[2];
                }
                $shippingCountrycode = $shippingAddress->getCountryId();
                $shippingCountry = $this->country->load($shippingCountrycode)->getName();
                $abandonedData['address'] = [
                    $address = 'billing_address' => [
                        'address' => $billingStreetAddress,
                        'city' => $billingAddress->getCity(),
                        'province' => $billingAddress->getRegion(),
                        'country' => $billingCountry,
                        'zip' => $billingAddress->getPostcode(),
                    ],
                    $address = 'shipping_address' => [
                        'address' => $shippingStreetAddress,
                        'city' => $shippingAddress->getCity(),
                        'province' => $shippingAddress->getRegion(),
                        'country' => $shippingCountry,
                        'zip' => $shippingAddress->getPostcode(),
                    ],
                ];
                $abandonedData['phone'] = $mobileNumber;
                $abandonedData['created_at'] = $abandonedCollectionData['created_at'];
                $this->helperData->businessOnBotCurl('POST', $url, $abandonedData);
            }
        }
    }
}
