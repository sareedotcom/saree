<?php

namespace Logicrays\OrderDeliveryEstimation\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as DefaultDateTime;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public const MODULE_ENABLE = 'orderdelivery/order/enable';
    public const GET_HOLIDAY_VALUE = 'orderdelivery/order/holiday';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param DefaultDateTime $date
     * @param CollectionFactory $optionCollection
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        DefaultDateTime $date,
        CollectionFactory $optionCollection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->date = $date;
        $this->optionCollection = $optionCollection;
    }

    /**
     * Check module enable or diable
     *
     * @return int
     */
    public function isEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope);
    }

    /**
     * Get holiday value fron system config
     *
     * @return int
     */
    public function getHoliday()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::GET_HOLIDAY_VALUE, $storeScope);
    }

    /**
     * Get delivery estimation date
     *
     * @param string $currentProduct
     * @param int $extraWorkingDays
     * @return void
     */
    public function getDeliveryEstimationDate($currentProduct, $extraWorkingDays = null)
    {
        $holidaysData = $this->getHoliday();
        $holidays = explode(',', $holidaysData);

        $timetodispatch = $currentProduct->getAttributeText('timetodispatch');
        if ($timetodispatch) {
            $dispatchDate = explode(" ", $timetodispatch);
            $data = [];
            foreach ($dispatchDate as $value) {
                if (is_numeric($value)) {
                    $data[] = $value;
                }
            }
            $currentTime = $this->timezone->date()->format('H:i a');
            $workingDays = max($data);
    
            // Get current Date
            $startDate = "";
            $currentDate = date("Y-m-d");
            $startDate = $currentDate;
    
            /**
            * If current time hours is greater than or equals to 03:00 PM
            * It will add 1 day plus
            */
            if (date('H') >= 15) {
                $startDate =  date("Y-m-d", time() + 86400);
            }
    
            $startDate =  $this->getStartDate($startDate, $holidays);
    
            if ($extraWorkingDays) {
                $workingDays = $workingDays + $extraWorkingDays;
            }
            $numberofWorkingDays = $workingDays;
    
             // Create DateTime object
             $dateTimeObject = $this->timezone->date(new \DateTime($startDate));
    
             $startDateTimeStamp = $this->date->gmtTimestamp($dateTimeObject);
    
            for ($i=1; $i<$numberofWorkingDays; $i++) {
    
                /**
                 * Add 1 day to timestamp
                */
                $addDay = 86400;
    
                /**
                 * Get what day it is next day
                * w - A numeric representation of the day (0 for Sunday, 6 for Saturday)
                */
                $nextDay = date('w', ($startDateTimeStamp+$addDay));
    
                /**
                 * If it's holidays get $i-1
                */
                if (in_array($nextDay, $holidays)) {
                    $i--;
                }
    
                // modify timestamp, add 1 day
                $startDateTimeStamp = $startDateTimeStamp+$addDay;
            }
    
            // Set TimeStamp
            $setTimeStampToFinalDate = $this->date->timestamp($startDateTimeStamp);
            // Define final date
            $finalDeliveryEstimationDate = null;
            $finalDeliveryEstimationDate = $this->date->date('l, d F Y', $setTimeStampToFinalDate);

            return $finalDeliveryEstimationDate;
        }
    }

    /**
     * Get option delivery estimation date
     *
     * @param array $currentProduct
     * @param string $extraWorkingDays
     * @return void
     */
    public function getOptionDeliveryDay($currentProduct, $extraWorkingDays = null)
    {
        $holidaysData = $this->getHoliday();
        $holidays = explode(',', $holidaysData);

        // Get current Date
        $startDate = "";
        $currentDate = date("Y-m-d");
        $startDate = $currentDate;

        /**
        * If current time hours is greater than or equals to 03:00 PM
        * It will add 1 day plus
        */
        if (date('H') >= 15) {
            $startDate =  date("Y-m-d", time() + 86400);
        }

        $startDate =  $this->getStartDate($startDate, $holidays);

        if ($extraWorkingDays) {
            $workingDays = $extraWorkingDays;
        } else {
            $timetodispatch = $currentProduct->getAttributeText('timetodispatch');
            $dispatchDate = explode(" ", $timetodispatch);
            $data = [];
            foreach ($dispatchDate as $value) {
                if (is_numeric($value)) {
                    $data[] = $value;
                }
            }

            $currentTime = $this->timezone->date()->format('H:i a');
            $workingDays = max($data);
        }
        $numberofWorkingDays = $workingDays;

        // Create DateTime object
        $dateTimeObject = $this->timezone->date(new \DateTime($startDate));

        $startDateTimeStamp = $this->date->gmtTimestamp($dateTimeObject);

        for ($i=1; $i<$numberofWorkingDays; $i++) {

            /**
             * Add 1 day to timestamp
            */
            $addDay = 86400;

            /**
             * Get what day it is next day
            * w - A numeric representation of the day (0 for Sunday, 6 for Saturday)
            */
            $nextDay = date('w', ($startDateTimeStamp+$addDay));

            /**
             * If it's holidays get $i-1
            */
            if (in_array($nextDay, $holidays)) {
                $i--;
            }

            // modify timestamp, add 1 day
            $startDateTimeStamp = $startDateTimeStamp+$addDay;
        }

        // Set TimeStamp
        $setTimeStampToFinalDate = $this->date->timestamp($startDateTimeStamp);
        // Define final date
        $finalDeliveryEstimationDate = null;
        $finalDeliveryEstimationDate = $this->date->date('l, d F Y', $setTimeStampToFinalDate);

        return $finalDeliveryEstimationDate;
    }

    /**
     * Get Start Date exclude holiday function
     *
     * @param mixed $startDate
     * @param array $holidays
     * @return void
     */
    public function getStartDate($startDate, $holidays)
    {
        $add_day = 0;

        do {
            $new_date = date('Y-m-d', strtotime("$startDate +$add_day Days"));
            $add_day++;
            $new_day_of_week = date('w', strtotime($new_date));
        } while (in_array($new_day_of_week, $holidays));

        $startDate = $new_date;
        return $startDate;
    }
}
