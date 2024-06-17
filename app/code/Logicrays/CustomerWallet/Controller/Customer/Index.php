<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * RedirectFactory variable
     *
     * @var redirectFactory
     */
    protected $resultRedirect;

    /**
     * Session variable
     *
     * @var customerSession
     */
    protected $customerSession;

    /**
     * PageFactory variable
     *
     * @var pageFactory
     */
    protected $pageFactory;

    /**
     * __construct function
     *
     * @param RedirectFactory $resultRedirect
     * @param Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        RedirectFactory $resultRedirect,
        Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->resultRedirect = $resultRedirect;
        $this->customerSession = $customerSession;
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return mixed
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirect->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        return $this->pageFactory->create();
    }
}
