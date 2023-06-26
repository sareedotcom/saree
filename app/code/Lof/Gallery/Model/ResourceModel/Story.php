<?php
namespace Lof\Gallery\Model\ResourceModel;

class Story extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('lof_gallery_banner', 'banner_id');
	}
	
}