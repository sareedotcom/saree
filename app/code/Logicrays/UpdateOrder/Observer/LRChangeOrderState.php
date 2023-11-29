<?php

namespace Logicrays\UpdateOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use \Magento\Sales\Model\OrderRepository;

class LRChangeOrderState implements ObserverInterface
{
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        State $state,
        AuthorizationInterface $authorization,
        OrderRepository $orderRepository
    ){
        $this->authorization = $authorization;
        $this->orderRepository = $orderRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
     
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $order = $observer->getEvent()->getOrder();
        if($order->getId()){
            $order1 = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($order->getId());     
            $statusArr = array("under_procurement","exchange","post_qc","pre_qc","under_smoothing","ready_to_ship","item_dispatch");
    
            if($order1->getState() == "new"){
                if(!$this->authorization->isAllowed('Logicrays_UpdateOrder::pending')){
                    throw new AuthorizationException(__('You don\'t have authorization to change status'));
                }
            }
            else if (in_array($order1->getStatus(), $statusArr)){
                if(!$this->authorization->isAllowed('Logicrays_UpdateOrder::under_procurement')){
                    throw new AuthorizationException(__('You don\'t have authorization to change status'));
                }
            }
            else if(!$this->authorization->isAllowed('Logicrays_UpdateOrder::'.$order1->getState())){
                throw new AuthorizationException(__('You don\'t have authorization to change status'));
            }   
        }

        return;
    }
}