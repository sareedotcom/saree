<?php
/**
 * Created by PhpStorm.
 * User: ksiamro
 * Date: 12.12.2016
 * Time: 15:49
 */

namespace Elsner\CheckOrder\Controller\Form;

use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('check_order');
        return $resultPage;
    }
}
