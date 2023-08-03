<?php

namespace MagicToolbox\MagicScroll\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Settings controller
 */
abstract class Settings extends \Magento\Backend\App\Action
{
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = null;

    /**
     * Data helper factory
     *
     * @var \MagicToolbox\MagicScroll\Helper\DataFactory
     */
    protected $dataHelperFactory = null;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MagicToolbox\MagicScroll\Helper\DataFactory $dataHelperFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MagicToolbox\MagicScroll\Helper\DataFactory $dataHelperFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelperFactory = $dataHelperFactory;
        parent::__construct($context);
    }

    /**
     * Check if admin has permissions to visit settings page
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MagicToolbox_MagicScroll::magicscroll_settings_edit');
    }

    /**
     * Get data helper
     *
     * @return \MagicToolbox\MagicScroll\Helper\Data
     */
    protected function getDataHelper()
    {
        static $helper = null;

        if ($helper == null) {
            $helper = $this->dataHelperFactory->create();
        }

        return $helper;
    }
}
