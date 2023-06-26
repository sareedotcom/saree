<?php

namespace MagicToolbox\MagicScroll\Controller\Adminhtml\Settings;

use MagicToolbox\MagicScroll\Controller\Adminhtml\Settings;

class Edit extends \MagicToolbox\MagicScroll\Controller\Adminhtml\Settings
{
    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MagicToolbox_MagicToolbox::magictoolbox');
        $title = $resultPage->getConfig()->getTitle();
        $title->prepend('Magic Toolbox');
        $title->prepend('Magic Scroll');
        return $resultPage;
    }
}
