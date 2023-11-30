<?php
namespace Logicrays\UpdateOrder\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MEASUREMENT_REMINDER_MAIL_TEMPLETE = "measurement/reminder/template";
    const MEASUREMENT_REMINDER_SENDER = "trans_email/ident_sales/email";
    const MEASUREMENT_REMINDER_SENDER_NAME = "trans_email/ident_sales/name";
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
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager=$storeManager;
        $this->logger = $context->getLogger();
    }

    public function sendEmail($data)
    {
        try {

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateId = $this->scopeConfig->getValue(self::MEASUREMENT_REMINDER_MAIL_TEMPLETE, $storeScope);
            $senderName = $this->scopeConfig->getValue(self::MEASUREMENT_REMINDER_SENDER_NAME, $storeScope);
            $sender = $this->scopeConfig->getValue(self::MEASUREMENT_REMINDER_SENDER, $storeScope);
            
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($senderName),
                'email' => $this->escaper->escapeHtml($sender),
            ];
            $orderLink = $this->_storeManager->getStore()->getBaseUrl()."sales/order/view/order_id/".$data['order_id'];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    "order_id" => $orderLink,
                    "customerEmail" => $data['customerEmail'],
                    "customerName" => $data['customerName'],
                    "incrementId" => $data['incrementId'],
                ])
                ->setFrom($sender)
                ->addTo($data['customerEmail'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}