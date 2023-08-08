<?php

namespace Logicrays\AbandonedChange\Observer;

class SetMobilenumber implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \Magento\Customer\Model\Customer $customers,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->customers = $customers;
        $this->customerFactory = $customerFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getData('order');
        if($order->getCustomerId()){

            $customerObj = $this->customers->load($order->getCustomerId());
            if(!$customerObj->getMobilenumber()){
                $billingAddress = $order->getBillingAddress();
                $customer = $this->customerFactory->create()->load($order->getCustomerId())->getDataModel();
                $customer->setCustomAttribute('mobilenumber', $billingAddress->getTelephone());
                $this->customerRepositoryInterface->save($customer);
            }
        }
    }
}       