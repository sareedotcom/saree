<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Controller\Account;

use Magento\Framework\View\Result\PageFactory;

/**
 * Account index controller
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Load layout
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
