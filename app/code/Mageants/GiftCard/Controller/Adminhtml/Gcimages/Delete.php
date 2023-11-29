<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Backend\App\Action\Context;
use \Mageants\GiftCard\Model\Templates;

/**
 * Delete Image Template
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Templates
     */
    public $template;

    /**
     * @param Context $context
     * @param Templates $template
     */
    public function __construct(
        Context $context,
        Templates $template
    ) {
        parent::__construct($context);
        $this->template = $template;
    }
    /**
     * Perform delete template action
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('image_id')!=''):
            $id = $this->getRequest()->getParam('image_id');
            $resultRedirect = $this->resultRedirectFactory->create();
            $row = $this->template->load($id);
            $row->delete();
            $this->messageManager->addSuccess(__('Template has been deleted.'));
            $this->_redirect('giftcertificate/gcimages/');
        endif;
    }
}
