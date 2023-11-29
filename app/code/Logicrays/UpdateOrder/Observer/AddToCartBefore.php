<?php
namespace Logicrays\UpdateOrder\Observer;

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\StateException;

class AddToCartBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * RestrictAddToCart Construct
     * 
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param CustomerSession $customerSession
     * @param ResourceConnection $resource
     */
    public function __construct(
        RedirectInterface $redirect,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        ResourceConnection $resource
    ) {
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->resource = $resource;
    }

    /**
     * Redirect to login page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {  
        if (!$this->customerSession->isLoggedIn()) {
            $requestInfo = $observer->getRequest()->getParams();
            if(isset($requestInfo['options'])){
                foreach($requestInfo['options'] AS $val){
                    if(!is_array($val) && $val != ""){
                        $connection = $this->resource->getConnection();
                        $select = $connection->select()
                            ->from(
                                ['cpott' => 'catalog_product_option_type_title'],
                                ['title']
                            )
                            ->where(
                                "cpott.option_type_id = :option_type_id"
                            );
                        $bind = ['option_type_id'=>$val];
                        $optionTitle = $connection->fetchOne($select, $bind);
                        if($optionTitle == "Later"){
                            $controller = $observer->getControllerAction();
                            $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
                            $this->messageManager->addError( __('Login is required for custom size: Later.') );
                            throw new StateException(__('Login is required for custom size: Later.'));
                        }
                    }
                }
            }
        }
    }
}