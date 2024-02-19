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
use \Magento\Sales\Model\OrderFactory;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Catalog\Model\CategoryFactory;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Pricing\Helper\Data as Pricingdata;
use \Mageants\GiftCard\Model\Templates;

/**
 * Notification class to send notification of expiration
 */
class Sendgiftcard
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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

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
     * @param OrderFactory $orderFactory
     * @param TimezoneInterface $timezone
     * @param CategoryFactory $categoryFactory
     * @param Repository $assetRepo
     * @param ProductRepositoryInterface $productrepository
     * @param StoreManagerInterface $storeManager
     * @param Pricingdata $pricingdata
     * @param Templates $template
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
        OrderFactory $orderFactory,
        TimezoneInterface $timezone,
        CategoryFactory $categoryFactory,
        Repository $assetRepo,
        ProductRepositoryInterface $productrepository,
        StoreManagerInterface $storeManager,
        Pricingdata $pricingdata,
        Templates $template,
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        $this->_helper =$helper;
        $this->_customer = $customer;
        $this->_account = $account;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_escaper = $escaper;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->orderFactory = $orderFactory;
        $this->_categoryFactory=$categoryFactory;
        $this->_assetRepo = $assetRepo;
        $this->productrepository = $productrepository;
        $this->_storeManager = $storeManager;
        $this->pricingdata = $pricingdata;
        $this->template = $template;
    }
    
    /**
     * Method executed when cron runs in server
     */

    public function execute()
    {
         $customerData = $this->_customer->getCollection()->addFieldToFilter('timezone', ['neq' => '']);
         $customerData->getSelect()->join(
             'gift_code_account',
             'main_table.customer_id = gift_code_account.order_id',
             ['*']
         );
            $this->_logger->debug('Running Cron from send class');
            $sendtime = str_replace(",", ":", $this->_helper->getSendTime());
            
            $time = date("H:i", strtotime($sendtime));
            
        foreach ($customerData->getData() as $customers) {
             
            $orderIncrementId = $customers['order_increment_id'];
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            $time_in_24_hour_format  = date("H:i", strtotime($this->timezone->formatDateTime($customers['timezone'])));
            if (!$customers['sentgiftcard']) {

                if ($customers['emailtime']) {
                    if ($time_in_24_hour_format >= $time &&
                        ($order->getStatus() != "canceled" &&
                            $order->getStatus() != "closed" &&
                            $order->getStatus() != "pending")) {
                        if ($this->_helper->isallowtimezone() == 1 && $this->_helper->isAllowDeliveryDate() == 1) {
                            $this->_logger->debug('templateData_if_condition');
                            
                            $this->cardTemplatevalue($customers, $order);

                        } else {
                            $this->_logger->debug('templateData_else_condition');
                            $priceHelper = $this->pricingdata;
                        
                            $this->setTemplatedata($customers);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Set card template value
     *
     * @param array $customers
     * @param object $order
     */
    public function cardTemplatevalue($customers, $order)
    {
        if ($customers['date_of_delivery'] == $this->timezone->date()->
            setTimezone(new \DateTimeZone($customers['timezone']))->format('Y-m-d')) {
            $priceHelper = $this->pricingdata;
        
            $formattedPrice = $priceHelper->currency($customers['current_balance'], true, false);
            $cats = explode(',', $customers['categories']);
            $categoryName='';
            foreach ($cats as $cat) {
                $_category = $this->_categoryFactory->create()->load($cat);
                $categoryName.=$_category->getName().",";
            }
            $templateVariable['message'] = "";
            
            $templateVariable['left'] = '0px';
            $templateVariable['top'] = '96px';
            $templateVariable['bgcolor'] = '#f00';
            $templateVariable['color'] = '#fff';

            if ($customers['sendtemplate_id']) {
                $templateData = $this->template->load(
                    $customers['sendtemplate_id']
                );
                if ($templateData->getPositionleft()) {
                    $templateVariable['left'] = $templateData->getPositionleft().'px';
                }
                if ($templateData->getPositiontop()) {
                    $templateVariable['top'] = $templateData->getPositiontop().'px';
                }
                if ($templateData->getColor()) {
                    $templateVariable['bgcolor'] = $templateData->getColor() ;
                }
                if ($templateData->getForecolor()) {
                    $templateVariable['color'] = $templateData->getForecolor() ;
                }
                if ($templateData->getMessage()) {
                    $templateVariable['message'] = $templateData->getMessage();
                }
            }

            if ($customers['message'] != "") {
                $templateVariable['message'] = $customers['message'];
            }

            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $productid = $item->getProductId();
            }

            if ($customers['custom_upload']) {

                $store = $this->_storeManager;
                $mediapath = $store->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                );
                $template_image = $mediapath."giftcertificate/".$customers['template'];
            } else {
                $template_image = $customers['template'];
            }

            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $productid = $item->getProductId();
            }

            if ($template_image=="") {
                $store = $this->_storeManager->getStore();
                $product = $this->productrepository->getById($productid);
                $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                if ($product->getImage()) {
                    $template_image = $mediaUrl . 'catalog/product' .$product->getImage();
                } else {
                    $template_image =  $this->_assetRepo->getUrl(
                        'Magento_Catalog::images/product/placeholder/image.jpg'
                    );
                }
            }

            $validity = $customers['expire_at'];
            if ($validity == '0000-00-00') {
                $validity = 'Unlimited';
            }
            
            $templateVariable['recipient_name'] = $customers['recipient_name'];
            $templateVariable['template'] = $template_image;
            $templateVariable['code'] = $customers['gift_code'];
            $templateVariable['sender_email'] = $customers['sender_email'];
            $templateVariable['sender_name'] = $customers['sender_name'];
            $templateVariable['current_balance'] = $formattedPrice;
            $templateVariable['category_name'] = $categoryName;
            $templateVariable['validity'] = $validity;
            $templateVariable['recipient_email'] = $customers['recipient_email'];
            
            $this->_helper->sendTemplate($templateVariable);
            
            $cust_collection = $this->_customer->load($customers['order_id']);
            $this->_customer->load($customers['order_id'])->setSentgiftcard(1)->save();
        }
    }

    /**
     * Set card template value
     *
     * @param array $customers
     */
    public function setTemplatedata($customers)
    {
        $formattedPrice = $priceHelper->currency($customers['current_balance'], true, false);
        $cats = explode(',', $customers['categories']);
        $categoryName='';
        foreach ($cats as $cat) {
            $_category = $this->_categoryFactory->create()->load($cat);
            $categoryName.=$_category->getName().",";
        }
        $templateVariable['message'] = "";
        
        $templateVariable['left'] = '0px';
        $templateVariable['top'] = '96px';
        $templateVariable['bgcolor'] = '#f00';
        $templateVariable['color'] = '#fff';

        if ($customers['sendtemplate_id']) {
          
            $templateData = $this->template->load(
                $customers['sendtemplate_id']
            );
            if ($templateData->getPositionleft()) {
                $templateVariable['left'] = $templateData->getPositionleft().'px';
            }
            if ($templateData->getPositiontop()) {
                $templateVariable['top'] = $templateData->getPositiontop().'px';
            }
            if ($templateData->getColor()) {
                $templateVariable['bgcolor'] = $templateData->getColor() ;
            }
            if ($templateData->getForecolor()) {
                $templateVariable['color'] = $templateData->getForecolor() ;
            }
            if ($templateData->getMessage()) {
                $templateVariable['message'] = $templateData->getMessage();
            }
        }
        
        if ($customers['comment']) {
            $templateVariable['message'] = $customers['comment'];
        }

        if ($customers['custom_upload']) {
            
            $store = $this->_storeManager;
            $mediapath = $store->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $template_image = $mediapath."giftcertificate/".$customers['template'];
        } else {
            $template_image = $customers['template'];
        }

        if ($template_image=="") {
            $store = $this->_storeManager->getStore();
            $product = $this->productrepository->getById($productid);
            $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            if ($product->getImage()) {
                $template_image = $mediaUrl . 'catalog/product' .$product->getImage();
            } else {
                $template_image =  $this->_assetRepo->getUrl(
                    'Magento_Catalog::images/product/placeholder/image.jpg'
                );
            }
        }

        $validity=$customers['expire_at'];
        if ($validity=='0000-00-00') {
            $validity='Unlimited';
        }
        
        $templateVariable['recipient_name'] = $customers['recipient_name'];
        $templateVariable['template'] = $template_image;
        $templateVariable['code'] = $customers['gift_code'];
        $templateVariable['sender_email'] = $customers['sender_email'];
        $templateVariable['sender_name'] = $customers['sender_name'];
        $templateVariable['current_balance'] = $formattedPrice;
        $templateVariable['category_name'] = $categoryName;
        $templateVariable['validity'] = $validity;
        $templateVariable['recipient_email'] = $customers['recipient_email'];
        
        $this->_helper->sendTemplate($templateVariable);
        
        $cust_collection = $this->_customer->load($customers['order_id']);
        $this->_customer->load($customers['order_id'])->setSentgiftcard(1)->save();
    }
}
