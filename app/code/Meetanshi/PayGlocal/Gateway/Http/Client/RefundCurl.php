<?php

namespace Meetanshi\PayGlocal\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Meetanshi\PayGlocal\Helper\Data;
use Meetanshi\PayGlocal\Helper\Logger as PayGlocalLogger;

/**
 * Class RefundCurl
 * @package Meetanshi\PayGlocal\Gateway\Http\Client
 */
class RefundCurl implements ClientInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var PayGlocalLogger
     */
    private $payglocalLogger;

    /**
     * RefundCurl constructor.
     * @param Data $helper
     * @param PayGlocalLogger $payglocalLogger
     */
    public function __construct(
        Data $helper,
        PayGlocalLogger $payglocalLogger
    )
    {
        $this->helper = $helper;
        $this->payglocalLogger = $payglocalLogger;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|mixed
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        try {
            $this->payglocalLogger->debug("Curl Request", $data);
            $gid = $data['gid'];

            $url = $this->helper->getGatewayUrl() . "/" . $gid . '/refund';
            $apiKey = $this->helper->getApiKey();
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$data['payload'],
                CURLOPT_HTTPHEADER => array(
                    'x-gl-auth: '.$apiKey,
                    'Content-Type: application/json'
                ),
            ));

            $statusResponse = curl_exec($curl);
            $statusData = json_decode($statusResponse, true);
            curl_close($curl);
            $this->payglocalLogger->debug("Curl responce final", $statusData);
            $response = $statusData;

        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            throw new LocalizedException(__($message));
        }
        return $response;
    }
}
