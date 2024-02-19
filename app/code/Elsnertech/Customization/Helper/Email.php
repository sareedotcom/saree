<?php
namespace Elsnertech\Customization\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ORDER_UPDATE_TO_AMDIN_MAIL_TEMPLETE = "orderupdate/comment/tousertemplate";
    const ORDER_UPDATE_TO_AMDIN_SENDER = "trans_email/ident_sales/email";
    const ORDER_UPDATE_TO_AMDIN_SENDER_NAME = "trans_email/ident_sales/name";
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

    public function sendEmail($comment,$updateBy, $orderId, $receiver)
    {
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Your text message');

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateId = $this->scopeConfig->getValue(self::ORDER_UPDATE_TO_AMDIN_MAIL_TEMPLETE, $storeScope);
            $senderName = $this->scopeConfig->getValue(self::ORDER_UPDATE_TO_AMDIN_SENDER_NAME, $storeScope);
            $sender = $this->scopeConfig->getValue(self::ORDER_UPDATE_TO_AMDIN_SENDER, $storeScope);
            
            $logger->info("AAAAAAAAA");
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($senderName),
                'email' => $this->escaper->escapeHtml($sender),
            ];
            $logger->info("BBBBBB");
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    "comment" => $comment,
                    "updateBy" => $updateBy,
                    "orderId" => $orderId
                ])
                ->setFrom($sender)
                ->addTo($receiver)
                ->getTransport();
            $logger->info("CCCCCC");
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $logger->info("DDDDDDDD");
        } catch (\Exception $e) {
            $logger->info($e->getMessage());
            $this->logger->debug($e->getMessage());
        }
    }
}