<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Index;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\LayoutFactory;

/**
 * Check gift code Details
 */
class Helperdata extends \Magento\Framework\App\Action\Action
{

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    /**
     *  Chek gift code and return detail of the code
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $data = $resultLayout->getLayout()->getBlock('gift_certificate')->getMinPrice();
        return $data;
    }
}
