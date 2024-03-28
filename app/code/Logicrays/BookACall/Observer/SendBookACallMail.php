<?php
namespace Logicrays\BookACall\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Logicrays\BookACall\Helper\Email;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;
use Magento\Directory\Model\CurrencyFactory;

class SendBookACallMail implements ObserverInterface
{   
    /**
     * @param Order $scopeConfig
     * @param Email $helper
     */
    public function __construct    (             
        Order $order,
        Email $helper,
        ProductRepository $productRepository,
        Image $imageHelper,
        CurrencyFactory $currencyFactory
    ) 
    {        
        $this->order = $order;  
        $this->helper = $helper;   
        $this->_productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->currencyCode = $currencyFactory->create();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        $order = $observer->getEvent()->getOrder();
        $order->save();
        $items = $order->getItems();
        $isMailSend = 0;
        
        $dateString = $order->getCreatedAtFormatted(2);
        $timestamp = strtotime($dateString);
        $formattedDate = date('F jS, Y, \a\t h:i A', $timestamp);
        
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $paymentMethodTitle = $method->getTitle();
        $istotalShow = 1;
        if(count($items) > 1){
            $istotalShow = 0;
        }
            
        $data = [];
        foreach ($items as $item) {
            $options = $item->getProductOptions();        
            if (isset($options['options']) && !empty($options['options'])) {
                foreach ($options['options'] as $option) {
                    if($option['label'] == "Country"){
                        
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $productsku = $item->getSku();
                        
                        $product = $this->_productRepository->get($item->getSku());
                        // $helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
                        $imageUrl = $this->imageHelper->init($product, 'product_page_image_small')
                                                ->setImageFile($product->getSmallImage())
                                                ->resize(380)
                                                ->getUrl();
                        
                        // $currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($order->getOrderCurrencyCode()); 
                        $currency = $this->currencyCode->load($order->getOrderCurrencyCode());
                        $currencySymbol = $currency->getCurrencySymbol();
                
                        $data['order_id'] = $order->getIncrementId();
                        $data['customerEmail'] = $order->getCustomerEmail();
                        $data['customerName'] = $order->getCustomerName();
                        $data['incrementId'] = $order->getIncrementId();
                        $data['country'] = $option['value'];
                        $data['orderDate'] = $formattedDate;
                        $data['imageUrl'] = $imageUrl;
                        $data['isShowTotal'] = $istotalShow;
                        $data['paymentMethodTitle'] = $paymentMethodTitle;
                        $data['subTotal'] = $currencySymbol.number_format($order->getSubtotal(),2);
                        $data['grandTotal'] = $currencySymbol.number_format($order->getGrandTotal(),2);
                        $data['price'] = $currencySymbol.number_format($item->getPrice(),2);
                        $data['increment_id'] = $order->getIncrementId();
                        $data['createdAtFormatted'] = $order->getCreatedAtFormatted(2);
                        $isMailSend = 1;
                    }
                    else if($option['label'] == "Name"){
                        $data['name'] = $option['value'];
                    }
                    else if($option['label'] == "Whatsapp Number"){
                        $data['number'] = $option['value'];
                    }
                    else if($option['label'] == "Outfits & Occasion"){
                        $data['outfitsandoccasion'] = $option['value'];
                    }
                    
                }
            }
        }
        if($isMailSend){
            $this->helper->sendEmail($data);
        }
    }
}