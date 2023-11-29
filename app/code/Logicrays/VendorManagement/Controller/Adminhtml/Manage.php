<?php

namespace Logicrays\VendorManagement\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Logicrays\VendorManagement\Model\VendorManagementFactory;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class adminhtml vendor base class
 */
abstract class Manage extends Action
{
    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Manage Constructor
     *
     * @param VendorManagementFactory $vendorFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        VendorManagementFactory $vendorFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Return Redirect
     *
     * @param Redirect $resultRedirect
     * @param mixed $paramCrudId
     * @return void
     */
    protected function _getBackResultRedirect(
        Redirect $resultRedirect,
        $paramCrudId = null
    ) {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        static::PARAM_CRUD_ID => $paramCrudId,
                        '_current' => true,
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new', ['_current' => true]);
                break;
            default:
                $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect;
    }

    /**
     * Init vendor
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initVendor()
    {
        $vendorId = (int)$this->getRequest()->getParam('vendor_id');
        $vendorFactory = $this->vendorFactory->create();
        if ($vendorId) {
            $vendorFactory->load($vendorId);
        }

        return $vendorFactory;
    }
}
