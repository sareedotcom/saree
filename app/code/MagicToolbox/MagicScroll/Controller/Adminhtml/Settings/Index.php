<?php

namespace MagicToolbox\MagicScroll\Controller\Adminhtml\Settings;

use MagicToolbox\MagicScroll\Controller\Adminhtml\Settings;

class Index extends \MagicToolbox\MagicScroll\Controller\Adminhtml\Settings
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('magicscroll/*/edit', ['active_tab' => $activeTab]);
        return $resultRedirect;
    }
}
