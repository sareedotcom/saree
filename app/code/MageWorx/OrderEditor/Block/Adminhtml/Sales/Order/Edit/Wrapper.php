<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit;

use Magento\Backend\Block\Template;

class Wrapper extends Template
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        \MageWorx\OrderEditor\Helper\Data $helperData,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->helperData     = $helperData;
        $this->coreRegistry      = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getJsonParamsItems()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/items'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_items')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsAddress()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/address'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_address')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsShipping()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/shipping'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_shipping')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsPayment()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/payment'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_payment')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsAccount()
    {
        $data = [
            'loadFormUrl'   => $this->getUrl('ordereditor/form/load'),
            'updateUrl'     => $this->getUrl('ordereditor/edit/account'),
            'renderGridUrl' => $this->getUrl('ordereditor/edit_account_widget/chooser'),
            'isAllowed'     => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_account')
        ];

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsInfo()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/info'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_info')
        ];

        return json_encode($data);
    }

    /**
     * @return bool
     */
    public function isEditAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageWorx_OrderEditor::edit_order');
    }
}
