<?php

namespace Meetanshi\PayGlocal\Gateway\Http\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Meetanshi\PayGlocal\Helper\Data;
use Meetanshi\PayGlocal\Helper\Logger as PayGlocalLogger;

/**
 * Class Curl
 * @package Meetanshi\PayGlocal\Gateway\Http\Client
 */
class Curl implements ClientInterface
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
     * Curl constructor.
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
     * @return array
     * @throws LocalizedException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        try {
            $this->payglocalLogger->debug("Curl Request", $data);
            $finalResponce = json_decode($data['payglocalResponce'],true);
            $this->payglocalLogger->debug("Curl Responce XGL", $finalResponce);

            if ($finalResponce["status"] == "SENT_FOR_CAPTURE") {

                $gid = $finalResponce['gid'];
                $curl = curl_init();

                $url = $this->helper->getGatewayUrl() . "/" . $gid . '/status';
                $apiKey = $this->helper->getApiKey();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'x-gl-auth: '.$apiKey
                    ),
                ));

                $statusResponse = curl_exec($curl);
                $statusData = json_decode($statusResponse, true);
                curl_close($curl);
                $this->payglocalLogger->debug("Curl responce final", $statusData);
                $response = $statusData['data'];

            } else {
                throw new LocalizedException(__("Sorry, but something went wrong"));
            }

        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            throw new LocalizedException(__($message));
        }
        return $response;
    }
}
