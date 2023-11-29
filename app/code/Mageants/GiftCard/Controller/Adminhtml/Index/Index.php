<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Index Controller for CodeList
 */
class Index extends \Mageants\GiftCard\Controller\Adminhtml\Index
{
    /**
     * Perform Index Action
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageants_GiftCard::giftcertificate');
        $resultPage->addBreadcrumb(__('Code List'), __('Code List'));
        $resultPage->addBreadcrumb(__('Code List'), __('Code List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Code List'));
        return $resultPage;
    }
}
