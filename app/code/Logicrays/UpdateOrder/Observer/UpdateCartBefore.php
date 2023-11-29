<?php
namespace Logicrays\UpdateOrder\Observer;

use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\StateException;

class UpdateCartBefore implements \Magento\Framework\Event\ObserverInterface
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
            $items = $observer->getEvent()->getCart()->getItems();
            foreach($items AS $val){
                $options = $val->getOptions();
                foreach ($options as $option) {
                    $optionData = $option->getData();
                    if(isset($optionData['value'])){
                        if($optionData['value'] != ""){
                            $connection = $this->resource->getConnection();
                            $select = $connection->select()
                                ->from(
                                    ['cpott' => 'catalog_product_option_type_title'],
                                    ['title']
                                )
                                ->where(
                                    "cpott.option_type_id = :option_type_id"
                                );
                            $bind = ['option_type_id'=>$optionData['value']];
                            $optionTitle = $connection->fetchOne($select, $bind);
                            if($optionTitle == "Later"){
                                $this->messageManager->addError( __('Login is required for custom size: Later.') );
                                throw new StateException(__('Login is required for custom size: Later.'));
                            }
                        }
                    }
                }
            }
        }
    }
}