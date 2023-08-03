<?php
/**
* Tatvic Software.
*
* @category  Tatvic
* @package   Tatvic_ActionableGoogleAnalytics
* @author    Tatvic
* @copyright Copyright (c) 2010-2021 Tatvic Software Private Limited (https://tatvic.com)
*/
namespace Tatvic\ActionableGoogleAnalytics\Block\Adminhtml\System;

/**
 * Class Extensions
 * @package WeltPixel\Backend\Block\Adminhtml\System\Config
 */
class Support extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '<script>var x = document.URL;</script>
		<div class="section-config">
		<div class="entry-edit-head admin__collapsible-block">
		<span id="tatvic_ee_support-link" class="entry-edit-head-link">
		</span><a id="tatvic_ee_support-head" href="#tatvic_ee_support-link" onclick="Fieldset.toggleCollapse(\'tatvic_ee_support\', \'x\'); return false;" class="">Installation Support</a></div>
		<input id="tatvic_ee_support-state" name="config_state[tatvic_ee_support]" type="hidden" value="0">
		<fieldset class="config admin__collapsible-block" id="tatvic_ee_support" style=""><legend>Conversion Settings</legend>
		<ul style="font-weight: 600;margin-left:25px;">
				<li><a style="font-size:18px;" href="https://marketplace.magento.com/media/catalog/product/tatvic-actionablegoogleanalytics-1-0-2-ce/installation_guides.pdf" target="_blank">Installation Instructions</a></li><br/>
				<li><a style="font-size:18px;" href="https://www.tatvic.com/contact/?utm_source=plugin_backend&utm_medium=magento2_premium_extension&utm_campaign=contact_us_magento2" target="_blank">Support</a></li><br/>
				<li><a style="font-size:18px;" href="https://www.tatvic.com/privacy-policy/?ref=plugin_policy&utm_source=plugin_backend&utm_medium=magento2_premium_extension&utm_campaign=GDPR_complaince_ecomm_plugins" target="_blank">Privacy Policy</a></li><br/>
			</ul>
		</fieldset>
		<script type="text/javascript">//<![CDATA[
		require(["prototype"], function(){Fieldset.applyCollapse("tatvic_ee_support");});
		//]]></script></div>';
    }
}