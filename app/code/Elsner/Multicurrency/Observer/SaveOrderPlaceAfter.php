<?php
namespace Elsner\Multicurrency\Observer;

use Magento\Framework\Event\ObserverInterface;

class SaveOrderPlaceAfter implements ObserverInterface
{
    protected $_multicurrency;
    protected $_helper;
    public function __construct(
        \Elsner\Multicurrency\Helper\Data $helper,
        \Elsner\Multicurrency\Model\Multicurrency $multicurrency
        )
    {
        $this->_helper = $helper;
        $this->_multicurrency = $multicurrency;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if(strpos($order->getPayment()->getMethod(), 'paypal') !== false){
            $data = $this->_multicurrency->getRowByIncrementId($order->getIncrementId())->getData();
            if(empty($data) !== true){
                $setData = $this->_multicurrency->load($data['multicurrency_id']);
                if($setData->getId()){
                    $setData->setAuthorizeTransactionId($order->getPayment()->getTransactionId());
                    $setData->save();    
                }
            }

        }
    }
}