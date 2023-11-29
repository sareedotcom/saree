<?php

namespace Mageants\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\App\Response\RedirectInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    public $redirect;

    /**
     * @var ResultFactory
     */
    public $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param ResultFactory $resultfactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        ResultFactory $resultfactory
    ) {
            $this->_messageManager = $messageManager;
            $this->resultRedirectFactory = $resultfactory;
            $this->redirect = $redirect;
    }
    
    /**
     * After save Product this code execute
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getProduct();
        $typedid=$data->getTypeId();
        if ($typedid == "giftcertificate") {

            $minprice = $observer->getProduct()->getCustomAttribute('minprice')->getValue();
            $img = $observer->getProduct()->getCustomAttribute('giftimages')->getValue();
            // $ctgry = $observer->getProduct()->getCustomAttribute('category')->getValue();
            $ctgry = $observer->getProduct()->getCustomAttribute('allowcategory')->getValue();
            $maxprice = $observer->getProduct()->getCustomAttribute('maxprice')->getValue();
            if ($minprice!=null) {
                  $productprice=$data['price'];
                if ($productprice < $minprice || $productprice > $maxprice) {
                    $this->_messageManager->addError(
                        "Make Sure The Product Price is between Minprice To Maxprice of Giftcard."
                    );
                    $backtoproductpage = $this->redirect->getRefererUrl();
                    return $backtoproductpage;

                }
            }
        }
    }
}
