<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Ui\Component\Listing\Column;

use Elasticsearch\Endpoints\Cluster\Reroute;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewOrder extends Column
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Logicrays\CustomerWallet\Helper\Data
     */
    protected $helperData;

    /**
     * __construct function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Logicrays\CustomerWallet\Helper\Data $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Logicrays\CustomerWallet\Helper\Data $helperData,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty(trim($item['orderid']))) {
                    $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' =>
                    $this->getOrderId($item['orderid'])]);
                    $link = '<a href="' . $url . ' " target="_blank">' . $item['orderid'] . '</a>';
                    $item['orderid'] = $link;
                } else {
                    $item['orderid'] = '--';
                }
            }
        }
        return $dataSource;
    }

    /**
     * GetOrderId function
     *
     * @param int $orderIncrementId
     * @return string
     */
    public function getOrderId($orderIncrementId)
    {
        return $this->helperData->getOrderId($orderIncrementId);
    }
}
