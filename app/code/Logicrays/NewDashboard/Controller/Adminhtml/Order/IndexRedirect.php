<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Logicrays\NewDashboard\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class IndexRedirect extends \Magento\Sales\Controller\Adminhtml\Order\Index implements HttpGetActionInterface
{
    /**
     * Orders grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_redirect('lrdes/order/index/');
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
        return $resultPage;
    }
}
