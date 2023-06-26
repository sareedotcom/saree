<?php
namespace Potato\Zendesk\Model\Source;

use Magento\Framework\Stdlib\DateTime\Timezone;

class ZendeskDate
{
    const DATA_ZULU_FORMAT = 'Y-m-d\TH:i:s+';

    const DATE_FORMAT_DEFAULT = 'Y, d M, H:i';
    const DATE_FORMAT_THIS_YEAR = 'd M, H:i';
    const DATE_FORMAT_TODAY = "H:i";
    const DATE_FORMAT_YESTERDAY = "H:i";

    /** @var Timezone  */
    protected $timezone;

    /**
     * @param Timezone $timezone
     */
    public function __construct(
        Timezone $timezone
    ) {
        $this->timezone = $timezone;
        return $this;
    }
    /**
     * @param string|\DateTime $date 
     * @return string
     */
    public function getFormattedDate($date)
    {
        $localDate = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        $currentDate = new \DateTime();
        $yesterdayDate = clone $currentDate;
        $yesterdayDate->modify('-1 day');
        $timezone = $this->timezone->getConfigTimezone();
        $timezoneClass = new \DateTimeZone($timezone);
        $localDate->setTimezone($timezoneClass);
        $currentDate->setTimezone($timezoneClass);
        $yesterdayDate->setTimezone($timezoneClass);

        if ($localDate->format('Y-m-d') == $currentDate->format('Y-m-d')) {
            $result = 'Today, ' . $localDate->format(self::DATE_FORMAT_TODAY);
        } elseif ($localDate->format('Y-m-d') == $yesterdayDate->format('Y-m-d')) {
            $result = 'Yesterday, ' . $localDate->format(self::DATE_FORMAT_YESTERDAY);
        } elseif ($localDate->format('Y') == $currentDate->format('Y')) {
            $result = $localDate->format(self::DATE_FORMAT_THIS_YEAR);
        } else {
            $result = $localDate->format(self::DATE_FORMAT_DEFAULT);
        }
        return $result;
    }
}