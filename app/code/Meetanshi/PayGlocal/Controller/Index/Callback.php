<?php

namespace Meetanshi\PayGlocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Meetanshi\PayGlocal\Helper\Data;

/**
 * Class Callback
 * @package Meetanshi\PayGlocal\Controller\Index
 */
class Callback extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Callback constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper
    )
    {
        $this->helper = $helper;
        $this->jsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->jsonFactory->create()->setData([
            'success' => true,
        ]);
    }
}
