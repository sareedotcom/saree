<?php

namespace Logicrays\PaymentLink\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Stripe test secret config path
     */
    const STRIPE_TEST_KEY = 'payment/stripe_payments_basic/stripe_test_sk';

    /**
     * Stripe live secret config path
     */
    const STRIPE_LIVE_KEY = 'payment/stripe_payments_basic/stripe_live_sk';

    /**
     * Stripe mode config path
     */
    const STRIPE_MODE = 'payment/stripe_payments_basic/stripe_mode';

    /**
     * Razorpay key id config path
     */
    const RAZRORPAY_KEY_ID = 'payment/razorpay/key_id';

    /**
     * Razorpay secret key config path
     */
    const RAZRORPAY_SECRET_KEY = 'payment/razorpay/key_secret';

    /**
     * Construct function
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->_order = $order;
        $this->_storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    public function sendStipePaymentLink($currency = 'INR', $minimumPay) {
            
            $length = strlen($minimumPay) + 2;
            $minimumPay = str_pad($minimumPay, $length, 0, STR_PAD_RIGHT);

            $curl = curl_init();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $stripeMode = $this->scopeConfig->getValue(self::STRIPE_MODE, $storeScope);
            
            if($stripeMode == 'live'){
                $secrateKey = $this->scopeConfig->getValue(self::STRIPE_LIVE_KEY, $storeScope);
            }
            else{
                $secrateKey = $this->scopeConfig->getValue(self::STRIPE_TEST_KEY, $storeScope);   
            }
            $decryptSecrateKey = $this->encryptor->decrypt($secrateKey);

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.stripe.com/v1/prices',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'currency='.$currency.'&unit_amount='.$minimumPay.'&product=1',
                CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded',
                        'Authorization: Bearer '.$decryptSecrateKey
                    ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            
            $res = json_decode($response);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.stripe.com/v1/payment_links',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'line_items%5B0%5D%5Bprice%5D='.$res->id.'&line_items%5B0%5D%5Bquantity%5D=1',
                CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Bearer '.$decryptSecrateKey
                ),
            ));
            
            $linkResponse = curl_exec($curl);
            curl_close($curl);
            $linkResponse = json_decode($linkResponse);
            return $linkResponse->url;

    }

    public function sendRozorPaymentLink($orderId, $minimumPay, $customerName, $mobilenumber, $customerEmail){
        $curl = curl_init();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $stripeKeyId = $this->scopeConfig->getValue(self::RAZRORPAY_KEY_ID, $storeScope);
        $stripeSecretKey = $this->scopeConfig->getValue(self::RAZRORPAY_SECRET_KEY, $storeScope);

        $length = strlen($minimumPay) + 2;
        $minimumPay = str_pad($minimumPay, $length, 0, STR_PAD_RIGHT);

        curl_setopt($curl, CURLOPT_USERPWD, $stripeKeyId . ":" . $stripeSecretKey);
        $reference_id = $orderId."_".time();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/payment_links/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "amount": '.$minimumPay.',
                "currency": "INR",
                "accept_partial": true,
                "reference_id": "'.$reference_id.'",
                "description": "Payment for Saree.com order no #'.$orderId.'",
                "customer": {
                    "name": "'.$customerName.'",
                    "contact": "'.$mobilenumber.'",
                    "email": "'.$customerEmail.'"
                },
                "notify": {
                    "sms": false,
                    "email": false,
                    "whatsapp":false
                },
                "reminder_enable": true,
                "callback_url": "https://saree.com/",
                "callback_method": "get"
            }',
            CURLOPT_HTTPHEADER => array(
                    'Content-type: application/json'
            ),
        ));

        $linkResponse = curl_exec($curl);
        curl_close($curl);
        $linkResponse = json_decode($linkResponse);

        $table_name = $this->connection->getTableName("razorpay_payment_link");
        $data = [
            "orderid" => $linkResponse->reference_id,
            "status" => $linkResponse->status,
            "short_url" => $linkResponse->short_url,
            "payments" => $linkResponse->payments
        ];
        $this->connection->insert($table_name, $data);

        return $linkResponse->short_url;
    }

    public function updateOrderAmountBaseOnPaymentLink(){
        
        $curl = curl_init();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $stripeKeyId = $this->scopeConfig->getValue(self::RAZRORPAY_KEY_ID, $storeScope);
        $stripeSecretKey = $this->scopeConfig->getValue(self::RAZRORPAY_SECRET_KEY, $storeScope);

        curl_setopt($curl, CURLOPT_USERPWD, $stripeKeyId . ":" . $stripeSecretKey);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/payment_links/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json'
            ),
        ));

        $linkResponse = curl_exec($curl);
        curl_close($curl);
        
        $linkResponse = json_decode($linkResponse);
        
        $sql = "SELECT orderid FROM `razorpay_payment_link` WHERE `status` != 'paid'";
        $result = $this->connection->fetchAll($sql);
        $reference_id = [];
        foreach ($result as $tableData) {
            $reference_id[] = $tableData['orderid'];
        }
        foreach ($linkResponse as $value) {
            foreach ($value as $value1) {
                // This case is only for full amount is paid of link
                if(in_array($value1->reference_id, $reference_id) && $value1->status == 'paid'){
                    
                    $orderIncrementId = substr($value1->reference_id,0,strpos($value1->reference_id, "_"));
                    if($orderIncrementId){
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $order = $this->_order->loadByIncrementId($orderIncrementId);
                        $paidAmount = substr($value1->amount_paid,0,strlen($value1->amount_paid)-2);
                        $order->setTotalPaid($paidAmount*$order->getBaseToOrderRate()); 
                        $order->setBaseTotalPaid($paidAmount);
                        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrency(); 
                        $currency = $this->currencyFactory->create()->load($order->getBaseCurrencyCode()); 
                        $currencySymbol = $currency->getCurrencySymbol();
                        $order->addStatusHistoryComment('Order payment is received amount of '.$currencySymbol.$paidAmount." using Razorpay");
                        $order->save();
                    }
                    $data = ["status"=>'paid',"payments" => json_encode($value1->payments)];
                    $where = ['orderid = ?' => $value1->reference_id];
                    $tableName = $this->connection->getTableName('razorpay_payment_link');
                    $updatedRows = $this->connection->update($tableName, $data, $where);
                }
            }
        }
    }
}
