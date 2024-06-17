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

namespace Logicrays\CustomerWallet\Ui\Component\Listing\CustomerWallet\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    public const URL_PATH_APPROVE = 'customerwallet/customerwallet/approve';
    public const URL_PATH_CANCEL = 'customerwallet/customerwallet/cancel';

    /**
     * UrlBuilder variable
     *
     * @var urlBuilder
     */
    protected $urlBuilder;

    /**
     * __construct function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * PrepareDataSource function
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id'])) {
                    $item[$this->getData('name')] = [
                        'approve' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_APPROVE,
                                [
                                    'id' => $item['id'],
                                ]
                            ),
                            'label' => __('Approve'),
                        ],
                        'cancel' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_CANCEL,
                                [
                                    'id' => $item['id'],
                                ]
                            ),
                            'label' => __('Cancel'),
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
