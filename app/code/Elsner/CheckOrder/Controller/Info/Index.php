<?php
/**
 * Created by PhpStorm.
 * User: ksiamro
 * Date: 12.12.2016
 * Time: 15:49
 */

namespace Elsner\CheckOrder\Controller\Info;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $customOrderModel;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Elsner\CheckOrder\Model\Order $customOrderModel
    ){
        $this->customOrderModel = $customOrderModel;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $orderSearchParams = [
            'increment_id' =>  trim($this->getRequest()->getParam('order_id')),
            'customer_email' => trim($this->getRequest()->getParam('email'))
        ];

        $resultPage = $this->customOrderModel->getOrderDataArray($orderSearchParams);

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultPage);
        return $resultJson;
    }
}
