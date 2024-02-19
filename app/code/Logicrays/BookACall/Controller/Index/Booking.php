<?php

namespace Logicrays\BookACall\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;


class Booking extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        /* Add below dependencies */
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Quote\Model\Quote $quoteModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        Cart $cart,
        ProductFactory $productFactory
    ) {
        $this->storeManager                 = $storeManager;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->quoteModel                   = $quoteModel;
        $this->productRepository            = $productRepository;
        $this->quoteFactory = $quoteFactory;
        $this->productFactory = $productFactory;
        $this->cart = $cart;
        parent::__construct($context);
    }


    /**
     * Booking action
     *
     * @return void
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = $objectManager->get('Magento\Framework\UrlInterface');
        

        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        $cart = $this->cart;

        if($customerSession->isLoggedIn()) {
            $customer = $this->_customerRepositoryInterface->getById($customerSession->getCustomer()->getId());
            $quote    = $this->quoteModel->loadByCustomer($customer);
            $quote->setCustomer($customer);
        }

        $bookACallIsAdded = 0;
        foreach ($cart->getItems() as $key => $value) {
            if($value->getSku() == 'book-a-call'){
                $bookACallIsAdded = 1;
            }
        }

        if(!$bookACallIsAdded){
            $aaaaa = $this->getRequest()->getParams();
            $country = $this->getRequest()->getParam('country');
            $outfitsandoccasion = $this->getRequest()->getParam('outfitsandoccasion');
            $wappnumber = $this->getRequest()->getParam('wappnumber');
            $name = $this->getRequest()->getParam('name');

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productId = $objectManager->get('Magento\Catalog\Model\Product')->getIdBySku('book-a-call');
            $product = $this->productFactory->create()->load($productId);

            $params = array();
            $options = array();
            $params['qty'] = 1;
            $params['product'] = $productId;
            
            $customOptionRepository = $objectManager->get('\Magento\Catalog\Api\ProductCustomOptionRepositoryInterface');
            foreach($customOptionRepository->getList('book-a-call') AS $option){
                if($option->getTitle() == 'Country'){
                    $pOption['Country'] = $option->getOptionId();
                }
                else if($option->getTitle() == 'Outfits & Occasion'){
                    $pOption['Outfits & Occasion'] = $option->getOptionId();
                }
                else if($option->getTitle() == 'Name'){
                    $pOption['Name'] = $option->getOptionId();
                }
                else if($option->getTitle() == 'Whatsapp Number'){
                    $pOption['Whatsapp Number'] = $option->getOptionId();
                }
            }
            $options[$pOption['Country']] = $country;
            $options[$pOption['Outfits & Occasion']] = $outfitsandoccasion;
            $options[$pOption['Name']] = $name;
            $options[$pOption['Whatsapp Number']] = $wappnumber;
            $params['options'] = $options;
            $cart->addProduct($product, $params);
            $cart->save();
            $cartUrl = $url->getUrl('checkout');
            $resultRedirect->setUrl($cartUrl);
            // $this->messageManager->addErrorMessage("You have already book one video call. If you want to chnage then please remove already added");
        }
        else{
            $cartUrl = $url->getUrl('exclusive-video-calling');
            $resultRedirect->setUrl($cartUrl);
            // $this->messageManager->addErrorMessage("You have already book one video call. If you want to chnage then please remove already added");
        }
     
        return $resultRedirect;

    }
}