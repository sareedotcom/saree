<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Controller\Adminhtml\Form;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Model\Edit\Quote as OrderEditorQuote;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\Order;
use MageWorx\OrderEditor\Model\Quote\Item;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Sales\Controller\Adminhtml\Order\Create;
use MageWorx\OrderEditor\Helper\Data as Helper;

/**
 * Class Options
 */
class Options extends Create
{
    /**
     * @var OrderEditorQuote $quote
     */
    protected $quote;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var InventoryDetectionStatusManager
     */
    protected $inventoryDetectionStatusManager;

    /**
     * Options constructor.
     *
     * @param Action\Context $context
     * @param ProductHelper $productHelper
     * @param Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param OrderEditorQuote $quote
     * @param Helper $helper
     * @param DataObjectFactory $dataObjectFactory
     * @param BackendSession $backendSession
     * @param OrderRepositoryInterface $orderRepository
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     */
    public function __construct(
        Action\Context $context,
        ProductHelper $productHelper,
        Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        OrderEditorQuote $quote,
        Helper $helper,
        DataObjectFactory $dataObjectFactory,
        BackendSession $backendSession,
        OrderRepositoryInterface $orderRepository,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager
    ) {
        $this->quote                           = $quote;
        $this->helper                          = $helper;
        $this->dataObjectFactory               = $dataObjectFactory;
        $this->backendSession                  = $backendSession;
        $this->orderRepository                 = $orderRepository;
        $this->inventoryDetectionStatusManager = $inventoryDetectionStatusManager;

        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * @return ResultRedirect
     */
    public function execute(): ResultRedirect
    {
        $updateResult = $this->dataObjectFactory->create();
        try {
            $orderItemId = $this->getRequest()->getParam('id');
            $params      = $this->getRequest()->getParams();

            $this->inventoryDetectionStatusManager->disableInventoryDetection();

            $prefixIdLength = strlen(Item::PREFIX_ID);
            if (substr($orderItemId, 0, $prefixIdLength) == Item::PREFIX_ID) {
                $quoteItemId = substr(
                    $orderItemId,
                    $prefixIdLength,
                    strlen($orderItemId)
                );
                $orderItem   = $this->quote->getUpdatedOrderItem($quoteItemId, $params);

            } else {
                  
                $orderItem = $this->quote->createNewOrderItem($orderItemId, $params);
                $orderItem->setId($orderItemId);
            }

            $this->inventoryDetectionStatusManager->enableInventoryDetection();

            $resultPage = $this->resultPageFactory->create();
            /** @var \Mageworx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Items\Options $optionsBlock */
            $optionsBlock = $resultPage->getLayout()
                                       ->getBlock('ordereditor_order_edit_form_items_options');
            if (!empty($optionsBlock)) {
                $optionsHtml = $optionsBlock
                    ->setOrderItem($orderItem)
                    ->toHtml();
                $updateResult->setOptionsHtml($optionsHtml);
            }
            $productOptions = $orderItem->getData('product_options');

            $options        = $this->helper->encodeBuyRequestValue($productOptions);

            $updateResult->setProductOptions($options);

            $orderId = $this->getRequest()->getParam('order_id');
            if (!$orderId) {
                throw new LocalizedException(__('Missed parameter: "%1"', 'order_id'));
            }
            /** @var Order $order */
            $order = $this->orderRepository->getById($orderId);

            $rate  = $order->getBaseCurrency()->getAnyRate($order->getOrderCurrency());
            $price = $orderItem->getData('base_price') * $rate;
            $updateResult->setPrice($price);
            $updateResult->setName($orderItem->getData('name'));
            $updateResult->setSku($orderItem->getData('sku'));
            $updateResult->setItemId($orderItemId);

            $updateResult->setOk(true);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $updateResult->setError(true);
            $updateResult->setMessage($errorMessage);
        }
        $jsVarName = $this->getRequest()->getParam('as_js_varname');
        $updateResult->setJsVarName($jsVarName);

        $this->backendSession->setCompositeProductResult($updateResult);

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory
            ->create()
            ->setPath('catalog/product/showUpdateResult');
        return $resultRedirect;
    }
}
