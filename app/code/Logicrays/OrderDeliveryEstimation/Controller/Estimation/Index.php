<?php

namespace Logicrays\OrderDeliveryEstimation\Controller\Estimation;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;
use Logicrays\OrderDeliveryEstimation\Helper\Data;

class Index extends Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param Registry $registry
     * @param ProductRepository $productRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Registry $registry,
        ProductRepository $productRepository,
        Data $helper
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->date = $date;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * Updated order estimation date
     *
     * @return json
     */
    public function execute()
    {
        $currentProductId = $this->context->getRequest()->getParam('product_id');
        $option = $this->context->getRequest()->getParam('option');
        $currentProduct = $this->productRepository->getById($currentProductId);

        if ($option == 'option') {
            $extraWorkingDays = 0;
            $updCateEstimationDate = $this->helper->getOptionDeliveryDay($currentProduct, $extraWorkingDays);
        } else {
            $extraWorkingDays = 0;
            $updCateEstimationDate = $this->helper->getDeliveryEstimationDate($currentProduct, $extraWorkingDays);
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["updateEstimationDate" => $updCateEstimationDate, "suceess" => true]);
        return $resultJson;
    }
}
