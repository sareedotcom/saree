<?php
namespace Elsnertech\Sms\Block;
 
class Init extends \Magento\Framework\View\Element\Template
{
    protected function _construct() {
		/** @var \Magento\Framework\App\ObjectManager $om */
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		/** @var \Magento\Framework\View\Page\Config $page */
		$page = $om->get('Magento\Framework\View\Page\Config');
		$page->addPageAsset('Elsnertech_Sms::css/styles.css');
	}
}