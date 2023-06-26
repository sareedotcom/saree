<?php

namespace Meetanshi\PayGlocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Meetanshi\PayGlocal\Helper\Data;

/**
 * Class Index
 * @package Meetanshi\PayGlocal\Controller\Index
 */
class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    )
    {
        $this->helper = $helper;
        $this->jsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            try {

                $quote = $this->helper->getCurrentQuote();
                $merchantUniqueId = $this->helper->generateRandomString(16);

                if (sizeof($quote->getBillingAddress()->getStreet()) >= 2) {
                    $addressStreetTwo = $quote->getBillingAddress()->getStreet()[1];
                } else {
                    $addressStreetTwo = "";
                }
                $orderItemData = [];
                foreach ($quote->getAllVisibleItems() as $orderItem) {
                    $orderItemData[] = [
                        "productDescription" => $orderItem->getName(),
                        "productSKU" => $orderItem->getSku(),
                        "productType" => $orderItem->getProductType(),
                        "itemUnitPrice" => round($orderItem->getBasePrice(), 2),
                        "itemQuantity" => round($orderItem->getQty()),
                    ];
                }

                if ($quote->getIsVirtual()) {
                    $shippingAddress = $quote->getBillingAddress();
                    if (sizeof($shippingAddress->getStreet()) >= 2) {
                        $addressShipStreetTwo = $shippingAddress->getStreet()[1];
                    } else {
                        $addressShipStreetTwo = "";
                    }
                } else {
                    $shippingAddress = $quote->getShippingAddress();
                    if (sizeof($shippingAddress->getStreet()) >= 2) {
                        $addressShipStreetTwo = $shippingAddress->getStreet()[1];
                    } else {
                        $addressShipStreetTwo = "";
                    }
                }

                if($quote->getCustomerId()){
                    $customerId =   $quote->getCustomerId();
                }else{
                    $customerId = "99999999";
                }
                
                if (!$quote->getReservedOrderId()) {
                    $quote->reserveOrderId();
                    $quote->setReservedOrderId($quote->getReservedOrderId())->save();
                }

                $payload = json_encode([
                    "merchantTxnId" => $quote->getReservedOrderId(),
                    "merchantUniqueId" => $quote->getId() . '_' . $merchantUniqueId,
                    "paymentData" => [
                        "totalAmount" => round($quote->getBaseGrandTotal(), 2),
                        "txnCurrency" => $quote->getBaseCurrencyCode(),
                        "billingData" => [
                            "firstName" => $quote->getBillingAddress()->getFirstname(),
                            "lastName" => $quote->getBillingAddress()->getLastname(),
                            "addressStreet1" => $quote->getBillingAddress()->getStreet()[0],
                            "addressStreet2" => $addressStreetTwo,
                            "addressCity" => $quote->getBillingAddress()->getCity(),
                            "addressState" => $quote->getBillingAddress()->getRegion(),
                            "addressPostalCode" => $quote->getBillingAddress()->getPostcode(),
                            "addressCountry" => $quote->getBillingAddress()->getCountryId(),
                            "emailId" => $quote->getCustomerEmail(),
                            "phoneNumber" => $quote->getBillingAddress()->getTelephone(),
                        ]
                    ],
                    "riskData" => [
                        "orderData" => $orderItemData,
                        "customerData" => [
                            "merchantAssignedCustomerId" => str_pad($customerId, 8, "0", STR_PAD_LEFT),
                            "customerAccountType" => "1",
                            "ipAddress" => $quote->getRemoteIp(),
                            "httpAccept" => $_SERVER['HTTP_ACCEPT'],
                            "httpUserAgent" => $_SERVER['HTTP_USER_AGENT'],
                        ],
                        "shippingData" => [
                            "firstName" => $shippingAddress->getFirstname(),
                            "lastName" => $shippingAddress->getLastname(),
                            "addressStreet1" => $shippingAddress->getStreet()[0],
                            "addressStreet2" => $addressShipStreetTwo,
                            "addressCity" => $shippingAddress->getCity(),
                            "addressState" => $shippingAddress->getRegion(),
                            "addressPostalCode" => $shippingAddress->getPostcode(),
                            "addressCountry" => $shippingAddress->getCountryId(),
                            "emailId" => $quote->getCustomerEmail(),
                            "phoneNumber" => $shippingAddress->getTelephone(),
                        ]
                    ],
                    "merchantCallbackURL" => $this->helper->getCallbackUrl()
                ]);

                $apiKey = $this->helper->getApiKey();
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $this->helper->getGatewayUrl() . '/initiate/paycollect',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => array(
                        'x-gl-auth: ' . $apiKey,
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                $data = json_decode($response, true);
                curl_close($curl);
                return $this->jsonFactory->create()->setData($data['data']);

            } catch (\Exception $e) {
                return $this->jsonFactory->create()->setData([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            }
        }

        return $this->jsonFactory->create()->setData([
            'error' => true,
            'message' => 'Something went wrong, please try again after sometimes.'
        ]);
    }
}
