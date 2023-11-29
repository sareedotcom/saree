<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Framework\Controller\ResultFactory;

/**
 * Image Template index adtion
 */
class Index extends \Mageants\GiftCard\Controller\Adminhtml\Index
{
    /**
     * Perform Index Action for template
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageants_GiftCard::giftcertificate');
        $resultPage->addBreadcrumb(__('Manage Template'), __('Manage Template'));
        $resultPage->addBreadcrumb(__('Manage Template'), __('Manage Template'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Template'));
        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_GiftCard::GiftCardImage');
    }
}
