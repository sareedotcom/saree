<?php

namespace Logicrays\AbandonedChange\Observer;

class AfterAddressSaveObserver implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerAddress = $observer->getEvent()->getCustomerAddress();

        if(!$customerAddress->getCustomer()->getMobilenumber())
        {
            $customerAddressData = $customerAddress->getData();
            $customer = $this->customerFactory->create()->load($customerAddress->getCustomer()->getId())->getDataModel();
            $customer->setCustomAttribute('mobilenumber', $customerAddressData['telephone']);
            $this->customerRepositoryInterface->save($customer);
        }
    }
}