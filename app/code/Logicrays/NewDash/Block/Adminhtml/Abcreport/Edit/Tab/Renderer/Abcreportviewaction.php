<?php

namespace Logicrays\NewDash\Block\Adminhtml\Abcreport\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class Abcreportviewaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Backend\Model\Url $backendUrlManager
     */
    public function __construct(
        \Magento\Backend\Model\Url $backendUrlManager
    ) {
        parent::__construct($context);
        $this->backendUrlManager  = $backendUrlManager;
    }
    
    public function render(DataObject $row)
    {
        $productId = $row->getCustomerId();
        $sku = $row->getCustomerId();
        $producturl =  $this->backendUrlManager->getUrl('customer/index/edit/id',['id' => $productId],'/active_tab/cart/');

        if (!empty($productId)){
            return '<a href="'.$producturl.'" target="_blank">View</a>';
        }
        else {
            return false;
        }
    }
}