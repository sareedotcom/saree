<?php

namespace Meetanshi\PayGlocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Meetanshi\PayGlocal\Helper\Data;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Status
 * @package Meetanshi\PayGlocal\Controller\Index
 */
class Status extends Action
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
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;


    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     * @param OrderFactory $orderFactory
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory
    ) {
        $this->helper = $helper;
        $this->jsonFactory = $resultJsonFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/pg_status.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__CLASS__ . '::' . __FUNCTION__ . ':: START');
        $data = $this->getRequest()->getParams();
        $logger->info(print_r($data, true));
        if (array_key_exists('status', $data)) {

            $statusUrl = $data['status'];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $statusUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET'
            ));

            $statusResponse = curl_exec($curl);
            $statusData = json_decode($statusResponse, true);

            $status = $statusData['status'];

            $logger->info(print_r($statusData, true));

            if ($status == 'SENT_FOR_CAPTURE') {
                $quote = $this->quoteFactory->create()->load($statusData['data']['merchantTxnId'], 'reserved_order_id');
                $payment = $quote->getPayment();

                $responseData = $statusData['data'];
                foreach ($responseData as $key => $value) {
                    $payment->setAdditionalInformation(
                        $key,
                        $value
                    );
                }
                return $this->jsonFactory->create()->setData([
                    'success' => true,
                ]);
            } else {
                return $this->jsonFactory->create()->setData([
                    'success' => false,
                    'message' => $statusData['message']
                ]);
            }
        }
    }
}
