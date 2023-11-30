<?php

namespace Logicrays\PaymentLink\Cron;

use Logicrays\PaymentLink\Helper\Data;

class CronUpdateTotalBaseOnLink
{
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Cron execute
     *
     * @return void
     */
    public function execute()
    {
        $this->helperData->updateOrderAmountBaseOnPaymentLink();
    }
}
