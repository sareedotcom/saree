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

class TrackingMode implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'universal_analytics','id' => 'universal_analytics', 'label' => __('Universal Analytics')],
    ['value' => 'google_analytics_4','id' => 'google_analytics_4', 'label' => __('Google Analytics 4')],
    ['value' => 'both','id' => 'both', 'label' => __('Both (UA + GA4) ')]
  ];
 }
}
