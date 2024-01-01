<?php
namespace Logicrays\VendorManagement\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const VENDOR_SEND_TO_VENDOR_MAIL_TEMPLETE =  "vendor/itemtomail/template";
    const VENDOR_SEND_TO_VENDOR_SENDER = "trans_email/ident_sales/email";
    const VENDOR_SEND_TO_VENDOR_SENDER_NAME = "trans_email/ident_sales/name";

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

            $templateId = $this->scopeConfig->getValue(self::VENDOR_SEND_TO_VENDOR_MAIL_TEMPLETE, $storeScope);
            $senderName = $this->scopeConfig->getValue(self::VENDOR_SEND_TO_VENDOR_SENDER_NAME, $storeScope);
            $sender = $this->scopeConfig->getValue(self::VENDOR_SEND_TO_VENDOR_SENDER, $storeScope);


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
                    "ponumber" => $data['ponumber'],
                    "vendor_name" => $data['vendor_name'],
                    "vendorPhone" => $data['vendorPhone'],
                    "vendor_code" => $data['vendor_code'],
                    "qty" => $data['qty'],
                    "comment_box" => $data['comment_box'],
                    "size" => $data['size'],
                    "orderIncrementId" => $data['orderIncrementId'],
                    "imageurl" => $data['imageurl'],
                ])
                ->setFrom($sender)
                ->addTo($data['vendorEmail'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}