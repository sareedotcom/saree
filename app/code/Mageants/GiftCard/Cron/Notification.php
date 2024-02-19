<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Cron;

use \Mageants\GiftCard\Helper\Data;
use \Mageants\GiftCard\Model\Customer;
use \Mageants\GiftCard\Model\Account;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Translate\Inline\StateInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Escaper;
use \Psr\Log\LoggerInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Notification class to send notification of expiration
 */
class Notification extends \Magento\Framework\View\Element\Template
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Mageants\GiftCard\Model\Customer
     */
    protected $_customer;
    
    /**
     * @var \Mageants\GiftCard\Model\Account
     */
    protected $_account;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Mageants\GiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @param Data $helper
     * @param Customer $customer
     * @param Account $account
     * @param DateTime $date
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param Escaper $escaper
     * @param StoreManagerInterface $storemanager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        Customer $customer,
        Account $account,
        DateTime $date,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        Escaper $escaper,
        StoreManagerInterface $storemanager,
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_helper =$helper;
        $this->_customer = $customer;
        $this->_account = $account;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_escaper = $escaper;
        $this->storemanager = $storemanager;
        $this->date = $date;
    }
    
    /**
     * Method executed when cron runs in server
     */
    public function execute()
    {
        if ($this->_helper->isNotify()) {
            $accountCollection = $this->_account->getCollection()->addFieldToFilter(
                'expire_at',
                ['neq' => '0000-00-00']
            );
            $notifyBefore = $this->_helper->notifyBefore();
            $orderIds= [];
             $accountCollection->getSelect()->join(
                 'gift_code_customer',
                 // note this join clause!
                 'main_table.order_id = gift_code_customer.customer_id',
                 ['*']
             );
             $daylen = 60*60*24;
            if ($accountCollection) {
                foreach ($accountCollection as $account) {
                    
                    $currentDate = strtotime($this->date->gmtDate('Y-m-d'));
                    $expirydate = strtotime($account->getExpireAt());

                    $len = ($expirydate-$currentDate)/$daylen;
                    if (((int)$notifyBefore == (int)$len) && !$account->getNotified()) {
                        $orderIds[]=$account->getOrderId();
                        $this->inlineTranslation->suspend();
                        try {
                            
                            $postObject = new \Magento\Framework\DataObject();
                            $currencysymbol = $this->storemanager;
                            $currency = $currencysymbol->getStore()->getCurrentCurrencyCode();
                            $codevalue = $currency.$account->getCodeValue();
                            $current_balance = $currency.$account->getCurrentBalance();
                            $template_image = $account->getTemplate();
                        
                            $template_image = $this->uploadImage($account, $currencysymbol);
                            
                            $_data = [
                                'name'=>$account->getRecipientName(),
                                'code'=>$account->getGiftCode(),
                                'expire_at'=>$account->getExpireAt(),
                                'template'=>$template_image,
                                'current_balance'=>$current_balance,
                                'code_value'=>$codevalue,
                                'sender_name'=>$account->getSenderName()
                            ];
                            $postObject->setData($_data);

                            $error = false;

                            $sender = [
                            'name' => $this->_escaper->escapeHtml($account->getSenderName()),
                            'email' => $this->_escaper->escapeHtml($account->getSenderEmail()),
                            ];

                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                            $transport = $this->_transportBuilder
                            ->setTemplateIdentifier($this->_helper->getNotifyTemplate())
                            ->setTemplateOptions(
                                [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($sender)
                            ->addTo($account->getRecipientEmail())
                            ->getTransport();

                            $transport->sendMessage();
                            $updateNotified = ['notified'=>1];

                            $this->_account->setData($updateNotified);
                            $this->_account->setAccountId($account->getAccountId());
                            $this->_account->save();
                            $this->inlineTranslation->resume();
                        } catch (\Exception $e) {
                            $this->inlineTranslation->resume();
                            $this->_logger->debug('Email can\'t be sent'.$e->getMessage());
                        }
                    }
                }
            }
         
        }
         
        $this->_logger->debug('Running Cron from Test class');
        return $this;
    }

    /**
     * Set media path
     *
     * @param object $account
     * @param object $currencysymbol
     */
    public function uploadImage($account, $currencysymbol)
    {
        if ($account->getCustomUpload()) {
            $mediapath = $currencysymbol->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $template_image = $mediapath."giftcertificate/".$account->getTemplate();
        }
        return $template_image;
    }
}
