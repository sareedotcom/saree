<?php

namespace Potato\Zendesk\Model\Source;

class TicketStatus
{
    const NEW_VALUE = 'new';
    const OPEN_VALUE = 'open';
    const PENDING_VALUE = 'pending';
    const HOLD_VALUE = 'hold';
    const SOLVED_VALUE = 'solved';
    const CLOSED_VALUE = 'closed';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::NEW_VALUE => __('New'),
            self::OPEN_VALUE => __('Open'),
            self::PENDING_VALUE => __('Pending'),
            self::HOLD_VALUE => __('Hold'),
            self::SOLVED_VALUE => __('Solved'),
            self::CLOSED_VALUE => __('Closed'),
        ];
    }

    /**
     * @param string $value
     * @return string
     */
    public function getStatusLabel($value)
    {
        $options = $this->getOptionArray();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return $value;
    }
}