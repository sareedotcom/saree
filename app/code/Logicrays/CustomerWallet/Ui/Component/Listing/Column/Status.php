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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    /**
     * @var \Logicrays\CustomerWallet\Model\Config\Source\Status
     */
    private $requestStatus;

    /**
     * __cons function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Logicrays\CustomerWallet\Model\Config\Source\Status $requestStatus
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Logicrays\CustomerWallet\Model\Config\Source\Status $requestStatus,
        array $components = [],
        array $data = []
    ) {
        $this->requestStatus = $requestStatus;
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
                $walletStatusCode = $this->getRequestStatus($item['status']);
                $status = '<span class="wallet_status_'.$walletStatusCode.'">' .$walletStatusCode . '</span>';
                $item['status'] = $status;
            }
        }
        return $dataSource;
    }

    /**
     * Get Request Status function
     *
     * @param string $walletStatusCode
     * @return string
     */
    public function getRequestStatus($walletStatusCode)
    {
        $label = '';
        $walletStatus = $this->requestStatus->toOptionArray();
        foreach ($walletStatus as $item) {
            if ($item['value'] == $walletStatusCode) {
                $label = (string) $item['label']->getText();
            }
        }
        return $label;
    }
}
