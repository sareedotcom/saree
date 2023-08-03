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

class ListMode implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'gtag','id' => 'gtag', 'label' => __('gtag.js')],
    ['value' => 'analytics','id' => 'analytics', 'label' => __('analytics.js')]
  ];
 }
}
