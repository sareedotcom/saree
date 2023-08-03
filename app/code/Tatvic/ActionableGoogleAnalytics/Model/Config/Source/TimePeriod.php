<?php
/**
* Tatvic Software.
*
* @category  Tatvic
* @package   Tatvic_ActionableGoogleAnalytics
* @author    Tatvic
* @copyright Copyright (c) 2010-2021 Tatvic Software Private Limited (https://tatvic.com)
*/
namespace Tatvic\ActionableGoogleAnalytics\Model\Config\Source;

class TimePeriod implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'year','id' => 'year', 'label' => __('Year')],
    ['value' => 'month','id' => 'month', 'label' => __('Month')],
    ['value' => 'day','id' => 'day', 'label' => __('Day')]
  ];
 }
}
