<?php

namespace Elsnertech\Sms\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Updatemobilenumber extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
	protected $objectManager;
	protected $customerFactory;
	protected $customerData;
	protected $customer;
	protected $customerResourceFactory;
	protected $customerResource;
    public function __construct(
		Context $context,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Customer\Model\Customer $customer,
		\Magento\Customer\Model\Data\Customer $customerData,
		\Magento\Customer\Model\ResourceModel\Customer $customerResource,
		\Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
		 $data = array()
		)
    {
 	    parent::__construct($context);

		$this->customerFactory	= $customerFactory;
		$this->customer	= $customer;
		$this->customerData	= $customerData;
		$this->customerResourceFactory = $customerResourceFactory;
		$this->customerResource = $customerResource;

    }
    public function execute()
    {
		$mobile = (string)$this->getRequest()->get('mobile');
		$customerId = (string)$this->getRequest()->get('userId');

    	$existCustomer = $this->customer->getCollection()->addFieldToFilter("mobilenumber", $mobile);
    	if(count($existCustomer) > 0){
    		$data = 0;
    	} else {
			$this->customerData = $this->customer->getDataModel();
			$this->customerData->setId($customerId);
			$this->customerData->setCustomAttribute('mobilenumber', $mobile);
			$this->customer->updateData($this->customerData);
			$this->customerResource = $this->customerResourceFactory->create();
			if ($mobile != "") {
			    $this->customerResource->saveAttribute($this->customer, 'mobilenumber');
			}
			$this->messageManager->addSuccess("Mobile Number Update successfully");
			$data = 1;
    	}

		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($data);
		return $resultJson;

    }
}