<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Mageants\GiftCard\Model\Templates;

/**
 * Add Image Template
 */
class Addtemplate extends \Magento\Backend\App\Action
{
    /**
     * @var Templates
     */
    public $template;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param Context $context
     * @param Templates $template
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Templates $template,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->template = $template;
    }
    
    /**
     * Perform AddTemplate controller Action
     */
    public function execute()
    {
        $imageid = (int) $this->getRequest()->getParam('image_id');
        $templateData = $this->template;
        
        if ($imageid) {
            $templateData = $templateData->load($imageid);
            $temptitle = $templateData->getImageTitle();
            if (!$templateData->getImageId()) {
                $this->messageManager->addError(__('Template no longer exist.'));
                $this->_redirect('giftcertificate/gcimages/index');
                return;
            }
        }
        $this->_coreRegistry->register('template_data', $templateData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $imageid ? __('Edit Template ').$temptitle : __('Add Template');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
