<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Response\Http\FileFactory;
use \Magento\Framework\Translate\InlineInterface;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\LayoutFactory;
use \Magento\Framework\Controller\Result\RawFactory;

/**
 * Abstract class of Index Action
 */
abstract class Index extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var RawFactory
     */
    public $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    public $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var InlineInterface
     */
    public $_translateInline;

    /**
     * @var FileFactory
     */
    public $_fileFactory;

    /**
     * @var \\Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * @inheritdoc
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_GiftCard::GiftCard');
    }
}
