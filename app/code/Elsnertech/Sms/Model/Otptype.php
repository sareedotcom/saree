<?php
namespace Elsnertech\Sms\Model;
class Otptype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'N', 'label' => __('Numeric Only')],
            ['value' => 'AN', 'label' => __('Alpha Numeric')]
			
        ];
    }
}
