<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Logicrays\NewDash\Model\Dashboard;

/**
 * Dashboard period info retriever
 */
class Period
{
    public const PERIOD_24_HOURS = '24h';
    public const PERIOD_30_DAYS = '30d';
    public const PERIOD_1_MONTH = '1m';
    public const PERIOD_6_MONTH = '6m';
    public const PERIOD_1_YEAR = '1y';
    public const PERIOD_2_YEARS = '2y';
    public const ALL_TIME = 'alltime';

    private const PERIOD_UNIT_HOUR = 'hour';
    private const PERIOD_UNIT_DAY = 'day';
    private const PERIOD_UNIT_MONTH = 'month';

    /**
     * Prepare array with periods for dashboard graphs
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return [
            static::PERIOD_30_DAYS => __('Last 30 Days'),
            static::PERIOD_1_MONTH => __('Current Month'),
            static::PERIOD_6_MONTH => __('Last 6 Month'),
            static::PERIOD_1_YEAR => __('YTD'),
            static::ALL_TIME => __('All Time')
        ];
    }

    /**
     * Prepare array with periods mapping to chart units
     *
     * @return array
     */
    public function getPeriodChartUnits(): array
    {
        return [
            static::PERIOD_24_HOURS => self::PERIOD_UNIT_HOUR,
            static::PERIOD_30_DAYS => self::PERIOD_UNIT_DAY,
            static::PERIOD_1_MONTH => self::PERIOD_UNIT_DAY,
            static::PERIOD_6_MONTH => self::PERIOD_UNIT_DAY,
            static::PERIOD_1_YEAR => self::PERIOD_UNIT_MONTH,
            static::PERIOD_2_YEARS => self::PERIOD_UNIT_MONTH,
            static::ALL_TIME => self::PERIOD_UNIT_MONTH,
        ];
    }
}
