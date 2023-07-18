<?php
namespace Logicrays\CodOrderManagement\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Twilio\Rest\ClientFactory;
use Twilio\Exceptions\RestException;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInt;
use Magento\Checkout\Model\Cart;

class OtpPopupData extends AbstractHelper
{
    protected $cart;
    /**
     * @param Context $context
     * @param ScopeConfigInt $_scopeConfig
     * @param ClientFactory $_twilioClient
     * @param EncryptorInterface $encryptor
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        ScopeConfigInt $_scopeConfig,
        ClientFactory $_twilioClient,
        EncryptorInterface $encryptor,
        Cart $cart
    ) {
        $this->_scopeConfig = $_scopeConfig;
        $this->encryptor = $encryptor;
        $this->_twilioClient = $_twilioClient;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Handles OTP Operation : Send & Verify
     *
     * @param string $operation
     * @param string $operationData
     * @return boolean
     */
    public function executeOtpOperation(string $operation, string $operationData = '')
    {
        $response = ['status' => false, 'message' => 'Unable to verify OTP, Please try again later.'];
        $isEnable = (boolean) $this->isOrderOtpPopupEnable();

        if (!in_array($operation, ['send', 'verify']) || !$isEnable) {
            $response['status'] = false;
            $response['message'] = 'Not a valid action.';
            return $response;
        }
       try {
            $id = $this->getTwilioAccountId();
            $token = $this->getTwilioAuthToken();
            $sid = $this->getTwilioServiceId();
            $shippingAddress = $this->cart->getQuote()->getShippingAddress();
            $country_code = $shippingAddress->getCountryId();
            $dial_code = $this->getphonecode($country_code);
            $telephone = '+'.$dial_code.$shippingAddress->getTelephone();
            $number = $telephone;
        } catch(Exception $e) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_test.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
        }
        try {
            $twilioObj = $this->_twilioClient->create(
                [
                    'username' => $id,
                    'password' => $token
                ]
            );
            if ($operation === 'send') {
                $twilioObj->verify->v2->services($sid)
                    ->verifications
                    ->create(
                        $number,
                        "sms"
                    );
                $response['status'] = true;
                $response['message'] = ($operationData === 'false') ? 'OTP sent successfully.' : 'OTP resent successfully';
            } elseif ($operation === 'verify' && !empty($operationData)) {
                $verification = $twilioObj->verify->v2->services($sid)
                    ->verificationChecks
                    ->create([
                        "to" => $number,
                        "code" => $operationData
                    ]);
                if (!empty($verification) && isset($verification->status) && $verification->status == 'approved') {
                    $response['status'] = true;
                    $response['message'] = 'OTP verified successfully.';
                } else {
                    $response['message'] = 'Incorrect OTP. Please enter a valid OTP.';
                }
            }
        } catch (Exception $exception) {
            $response['message'] = 'Technical Error, Please try again later.';
        } catch (RestException $restException) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_test.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(json_encode($restException->getMessage()));
            $response['message'] = 'Maximum attempts exhausted, Please try again after 10 minutes.';
        }
        return $response;
    }

    /**
     * Gets config value
     *
     * @return string
     */
    public function getTwilioAccountId()
    {
        return $this->_scopeConfig->getValue('logicrays/order_otp_verification/twilio_account_id');
    }

    /**
     * Gets config value
     *
     * @return string
     */
    public function getTwilioAuthToken()
    {
        $value = $this->_scopeConfig->getValue('logicrays/order_otp_verification/twilio_auth_token');
        return $this->encryptor->decrypt($value);
    }

    /**
     * Gets config value
     *
     * @return string
     */
    public function getTwilioServiceId()
    {
        return $this->_scopeConfig->getValue('logicrays/order_otp_verification/twilio_service_id');
    }

    public function isOrderOtpPopupEnable()
    {
        return $this->_scopeConfig->getValue('logicrays/order_otp_verification/enable');
    }

    public function getPaymentLink()
    {
        return $this->_scopeConfig->getValue('logicrays/email_payment_link/payment_link');
    }

    public function verifyOrderOtpPopupConfig($selectedPaymentMethod)
    {
        $isEnable = (boolean) $this->isOrderOtpPopupEnable();
        $allowedPaymentMethod = 'cashondelivery';

        if ($selectedPaymentMethod == $allowedPaymentMethod && $isEnable) {
            return true;
        }
        return false;
    }

    /**
     * Gets dial code based on country
     */
    public function getphonecode($country_code)
    {
        $countrycode = array(
            'AD'=>'376',
            'AE'=>'971',
            'AF'=>'93',
            'AG'=>'1268',
            'AI'=>'1264',
            'AL'=>'355',
            'AM'=>'374',
            'AN'=>'599',
            'AO'=>'244',
            'AQ'=>'672',
            'AR'=>'54',
            'AS'=>'1684',
            'AT'=>'43',
            'AU'=>'61',
            'AW'=>'297',
            'AZ'=>'994',
            'BA'=>'387',
            'BB'=>'1246',
            'BD'=>'880',
            'BE'=>'32',
            'BF'=>'226',
            'BG'=>'359',
            'BH'=>'973',
            'BI'=>'257',
            'BJ'=>'229',
            'BL'=>'590',
            'BM'=>'1441',
            'BN'=>'673',
            'BO'=>'591',
            'BR'=>'55',
            'BS'=>'1242',
            'BT'=>'975',
            'BW'=>'267',
            'BY'=>'375',
            'BZ'=>'501',
            'CA'=>'1',
            'CC'=>'61',
            'CD'=>'243',
            'CF'=>'236',
            'CG'=>'242',
            'CH'=>'41',
            'CI'=>'225',
            'CK'=>'682',
            'CL'=>'56',
            'CM'=>'237',
            'CN'=>'86',
            'CO'=>'57',
            'CR'=>'506',
            'CU'=>'53',
            'CV'=>'238',
            'CX'=>'61',
            'CY'=>'357',
            'CZ'=>'420',
            'DE'=>'49',
            'DJ'=>'253',
            'DK'=>'45',
            'DM'=>'1767',
            'DO'=>'1809',
            'DZ'=>'213',
            'EC'=>'593',
            'EE'=>'372',
            'EG'=>'20',
            'ER'=>'291',
            'ES'=>'34',
            'ET'=>'251',
            'FI'=>'358',
            'FJ'=>'679',
            'FK'=>'500',
            'FM'=>'691',
            'FO'=>'298',
            'FR'=>'33',
            'GA'=>'241',
            'GB'=>'44',
            'GD'=>'1473',
            'GE'=>'995',
            'GH'=>'233',
            'GI'=>'350',
            'GL'=>'299',
            'GM'=>'220',
            'GN'=>'224',
            'GQ'=>'240',
            'GR'=>'30',
            'GT'=>'502',
            'GU'=>'1671',
            'GW'=>'245',
            'GY'=>'592',
            'HK'=>'852',
            'HN'=>'504',
            'HR'=>'385',
            'HT'=>'509',
            'HU'=>'36',
            'ID'=>'62',
            'IE'=>'353',
            'IL'=>'972',
            'IM'=>'44',
            'IN'=>'91',
            'IQ'=>'964',
            'IR'=>'98',
            'IS'=>'354',
            'IT'=>'39',
            'JM'=>'1876',
            'JO'=>'962',
            'JP'=>'81',
            'KE'=>'254',
            'KG'=>'996',
            'KH'=>'855',
            'KI'=>'686',
            'KM'=>'269',
            'KN'=>'1869',
            'KP'=>'850',
            'KR'=>'82',
            'KW'=>'965',
            'KY'=>'1345',
            'KZ'=>'7',
            'LA'=>'856',
            'LB'=>'961',
            'LC'=>'1758',
            'LI'=>'423',
            'LK'=>'94',
            'LR'=>'231',
            'LS'=>'266',
            'LT'=>'370',
            'LU'=>'352',
            'LV'=>'371',
            'LY'=>'218',
            'MA'=>'212',
            'MC'=>'377',
            'MD'=>'373',
            'ME'=>'382',
            'MF'=>'1599',
            'MG'=>'261',
            'MH'=>'692',
            'MK'=>'389',
            'ML'=>'223',
            'MM'=>'95',
            'MN'=>'976',
            'MO'=>'853',
            'MP'=>'1670',
            'MR'=>'222',
            'MS'=>'1664',
            'MT'=>'356',
            'MU'=>'230',
            'MV'=>'960',
            'MW'=>'265',
            'MX'=>'52',
            'MY'=>'60',
            'MZ'=>'258',
            'NA'=>'264',
            'NC'=>'687',
            'NE'=>'227',
            'NG'=>'234',
            'NI'=>'505',
            'NL'=>'31',
            'NO'=>'47',
            'NP'=>'977',
            'NR'=>'674',
            'NU'=>'683',
            'NZ'=>'64',
            'OM'=>'968',
            'PA'=>'507',
            'PE'=>'51',
            'PF'=>'689',
            'PG'=>'675',
            'PH'=>'63',
            'PK'=>'92',
            'PL'=>'48',
            'PM'=>'508',
            'PN'=>'870',
            'PR'=>'1',
            'PT'=>'351',
            'PW'=>'680',
            'PY'=>'595',
            'QA'=>'974',
            'RO'=>'40',
            'RS'=>'381',
            'RU'=>'7',
            'RW'=>'250',
            'SA'=>'966',
            'SB'=>'677',
            'SC'=>'248',
            'SD'=>'249',
            'SE'=>'46',
            'SG'=>'65',
            'SH'=>'290',
            'SI'=>'386',
            'SK'=>'421',
            'SL'=>'232',
            'SM'=>'378',
            'SN'=>'221',
            'SO'=>'252',
            'SR'=>'597',
            'ST'=>'239',
            'SV'=>'503',
            'SY'=>'963',
            'SZ'=>'268',
            'TC'=>'1649',
            'TD'=>'235',
            'TG'=>'228',
            'TH'=>'66',
            'TJ'=>'992',
            'TK'=>'690',
            'TL'=>'670',
            'TM'=>'993',
            'TN'=>'216',
            'TO'=>'676',
            'TR'=>'90',
            'TT'=>'1868',
            'TV'=>'688',
            'TW'=>'886',
            'TZ'=>'255',
            'UA'=>'380',
            'UG'=>'256',
            'US'=>'1',
            'UY'=>'598',
            'UZ'=>'998',
            'VA'=>'39',
            'VC'=>'1784',
            'VE'=>'58',
            'VG'=>'1284',
            'VI'=>'1340',
            'VN'=>'84',
            'VU'=>'678',
            'WF'=>'681',
            'WS'=>'685',
            'XK'=>'381',
            'YE'=>'967',
            'YT'=>'262',
            'ZA'=>'27',
            'ZM'=>'260',
            'ZW'=>'263'
        );
        if(isset($countrycode[$country_code])){
            $dial_code = $countrycode[$country_code];
        }
        return $dial_code;
    }
}
