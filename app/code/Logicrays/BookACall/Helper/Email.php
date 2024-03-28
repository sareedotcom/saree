<?php
namespace Logicrays\BookACall\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BOOKACALL_MAIL_TEMPLATE =  "bookacall/general/template";
    const BOOKACALL_MAIL_SENDERNAME = "bookacall/general/sendername";
    const BOOKACALL_MAIL_SENDER = "bookacall/general/sender";
    const BOOKACALL_MAIL_RECEIVER = "bookacall/general/receiver";

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

            $templateId = $this->scopeConfig->getValue(self::BOOKACALL_MAIL_TEMPLATE, $storeScope);
            $senderName = $this->scopeConfig->getValue(self::BOOKACALL_MAIL_SENDERNAME, $storeScope);
            $sender = $this->scopeConfig->getValue(self::BOOKACALL_MAIL_SENDER, $storeScope);
            $receiver = $this->scopeConfig->getValue(self::BOOKACALL_MAIL_RECEIVER, $storeScope);
            
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
                    "name" => $data['name'],
                    "country" => $data['country'],
                    "orderId" => $data['order_id'],
                    "number" => $data['number'],
                    "outfitsandoccasion" => $data['outfitsandoccasion'],
                    "customerName" => $data['name'],
                    "imageUrl" => $data['imageUrl'],
                    "isShowTotal" => $data['isShowTotal'],
                    "paymentMethodTitle" => $data['paymentMethodTitle'],
                    "subTotal" => $data['subTotal'],
                    "grandTotal" => $data['grandTotal'],
                    "price" => $data['price'],
                    "increment_id" => $data['increment_id'],
                    "createdAtFormatted" => $data['createdAtFormatted'],
                ])
                ->setFrom($sender)
                ->addTo($receiver)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
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
                    "name" => $data['name'],
                    "country" => $data['country'],
                    "orderId" => $data['order_id'],
                    "number" => $data['number'],
                    "outfitsandoccasion" => $data['outfitsandoccasion'],
                    "customerName" => $data['name'],
                    "imageUrl" => $data['imageUrl'],
                    "isShowTotal" => $data['isShowTotal'],
                    "paymentMethodTitle" => $data['paymentMethodTitle'],
                    "subTotal" => $data['subTotal'],
                    "grandTotal" => $data['grandTotal'],
                    "price" => $data['price'],
                    "increment_id" => $data['increment_id'],
                    "createdAtFormatted" => $data['createdAtFormatted'],
                ])
                ->setFrom($sender)
                ->addTo($data['customerEmail'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://saree.bobot.in/workflow/webhook/3ac3b0e6-5fd5-48d0-a25a-e6da77ad6182',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "platform": "saree",
                    "phone": "'.$data['number'].'",
                    "variables": {"name":"'.$data['name'].'","incrementid":"'.$data['incrementId'].'","orderdate":"'.$data['orderDate'].'"},
                    "noOfVariables": 4
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