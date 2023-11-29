<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Logicrays\NewDash\Model\ResourceModel\Order;

use Magento\Framework\DB\Select;

/**
 * Reports orders collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Is live
     *
     * @var bool
     */
    protected $_isLive = false;

    /**
     * Sales amount expression
     *
     * @var string
     */
    protected $_salesAmountExpression;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager instance
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Locale date instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Order config instance
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * Reports order factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Report\OrderFactory
     */
    protected $_reportOrderFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper $coreResourceHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Report\OrderFactory $reportOrderFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $scopeConfig,
            $storeManager,
            $localeDate,
            $orderConfig,
            $reportOrderFactory,
            $connection,
            $resource
        );
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_orderConfig = $orderConfig;
        $this->_reportOrderFactory = $reportOrderFactory;
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param bool $returnObjects
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new \DateTime();
        $dateStart = new \DateTime();

        // go to the end of a day
        $dateEnd->setTime(23, 59, 59);

        $dateStart->setTime(0, 0, 0);

        switch ($range) {
            case '24h':
                $dateEnd = new \DateTime();
                $dateEnd->modify('+1 hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1 day');
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 days');
                break;

            case '30d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->modify('-30 days');
                break;

            case '1m':
                $dateStart->setDate(
                    $dateStart->format('Y'),
                    $dateStart->format('m'),
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/mtd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                break;

            case '6m':
                // substract 6 months we need to include
                // only today and not hte last one from range
                $dateStart->modify('-6 months');
                break;

            case 'alltime':
                // substract 500 years we need to include
                // only today and not hte last one from range
                $dateStart->modify('-500  years');
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateStart;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->_scopeConfig->getValue(
                        'reports/dashboard/ytd_start',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setDate($dateStart->format('Y'), $startMonth, $startDay);
                $dateStart->modify('-1 year');
                if ($range == '2y') {
                    $dateStart->modify('-1 year');
                }
                break;
        }

        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        } else {
            return ['from' => $dateStart, 'to' => $dateEnd, 'datetime' => true];
        }
    }

    /**
     * Get SQL expression for totals with discount refunded.
     *
     * @param int $storeId
     * @param string $baseSubtotalRefunded
     * @param string $baseSubtotalCanceled
     * @param string $baseDiscountRefunded
     * @param string $baseDiscountCanceled
     * @return string
     */
    private function getTotalsExpressionWithDiscountRefunded(
        $storeId,
        $baseSubtotalRefunded,
        $baseSubtotalCanceled,
        $baseDiscountRefunded,
        $baseDiscountCanceled
    ) {
        $template = ($storeId != 0)
            ? '(main_table.base_subtotal - %2$s - %1$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s))'
            : '((main_table.base_subtotal - %1$s - %2$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s)) '
                . ' * main_table.base_to_global_rate)';
        return sprintf(
            $template,
            $baseSubtotalRefunded,
            $baseSubtotalCanceled,
            $baseDiscountRefunded,
            $baseDiscountCanceled
        );
    }
}
