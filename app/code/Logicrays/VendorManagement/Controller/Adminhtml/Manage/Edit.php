<?php

namespace Logicrays\VendorManagement\Controller\Adminhtml\Manage;

use Logicrays\VendorManagement\Controller\Adminhtml\Manage;
use Logicrays\VendorManagement\Model\VendorManagementFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Backend\Model\Session;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class adminhtml vendor edit action
 */
class Edit extends Manage
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var Session
     */
    protected $adminSession;

    /**
     * @param VendorManagementFactory $vendorFactory
     * @param Registry $registry
     * @param Session $adminSession
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        VendorManagementFactory $vendorFactory,
        Registry $registry,
        Session $adminSession,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->_coreRegistry = $registry;
        $this->adminSession = $adminSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($vendorFactory, $registry, $context);
    }

    /**
     * Edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $vendor = $this->initVendor();

        if ($id) {
            $vendor->load($id);
            if (!$vendor->getVendorId()) {
                $this->messageManager->addError(__('This vendor no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'vendor/manage/edit',
                    [
                        'id' => $vendor->getVendorId(),
                        '_current' => true,
                    ]
                );

                return $resultRedirect;
            }
        }

        $data = $this->adminSession->getFormData(true);
        if (!empty($data)) {
            $vendor->setData($data);
        }

        $this->_coreRegistry->register('logicrays_vendor_form', $vendor);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Logicrays_VendorManagement::grid');
        $resultPage->getConfig()->getTitle()
            ->set(__('Edit Vendor'))
            ->prepend($vendor->getVendorId() ? "Edit " .
                $vendor->getFirstname() . " " .
                $vendor->getLastname() : __('New Vendor'));
        return $resultPage;
    }
}
