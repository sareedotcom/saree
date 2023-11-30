<?php
namespace Logicrays\PaymentLink\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PAYMENT_REMINDER_MAIL_TEMPLETE =  "payement/reminder/template";
    const PAYMENT_REMINDER_SENDER_NAME = "payement/reminder/sendername";
    const PAYMENT_REMINDER_SENDER = "payement/reminder/sender";

    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $context->getLogger();
    }

    public function sendEmail($data)
    {
        try {

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $templateId = $this->scopeConfig->getValue(self::PAYMENT_REMINDER_MAIL_TEMPLETE, $storeScope);
            $senderName = $this->scopeConfig->getValue(self::PAYMENT_REMINDER_SENDER_NAME, $storeScope);
            $sender = $this->scopeConfig->getValue(self::PAYMENT_REMINDER_SENDER, $storeScope);


            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($senderName),
                'email' => $this->escaper->escapeHtml($sender),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    "grandTotal" => number_format($data['grandTotal'],2),
                    "currency" => $data['currency'],
                    "orderId" => $data['orderId'],
                    "customerName" => $data['customerName'],
                    "paymentlink" => $data['paymentlinkhide'],
                    "minimumPay" => number_format($data['minimumPay'],2),
                    "paymenttype" => $data['paymenttype']
                ])
                ->setFrom($sender)
                ->addTo($data['customerEmail'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
            		CURLOPT_URL => 'https://saree.bobot.in/workflow/webhook/2203f5b8-1b34-4d83-a3f0-d9f60e92359b',
            		CURLOPT_RETURNTRANSFER => true,
            		CURLOPT_ENCODING => '',
            		CURLOPT_MAXREDIRS => 10,
            		CURLOPT_TIMEOUT => 0,
            		CURLOPT_FOLLOWLOCATION => true,
            		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            		CURLOPT_CUSTOMREQUEST => 'POST',
            		CURLOPT_POSTFIELDS =>'
                {
                    "platform": "saree",
                    "phone": '.$data['mobilenumber'].',
                    "variables": {"customername":"aaaa'.$data['customerName'].'","plink":"'.$data['paymentlinkhide'].'"},
                    "noOfVariables": 2
                }',
            		CURLOPT_HTTPHEADER => array(
            				'Content-Type: application/json'
            		),
            ));
            
            $response = curl_exec($curl);
            curl_close($curl);
        
        
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}