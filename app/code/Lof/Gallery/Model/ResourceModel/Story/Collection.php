<?php
namespace Lof\Gallery\Model\ResourceModel\Post;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'banner_id';
	protected $_eventPrefix = 'lof_gallery_banner_collection';
	protected $_eventObject = 'post_collection';

	protected function _construct()
	{
		$this->_init('Lof\Gallery\Model\Story', 'Lof\Gallery\Model\ResourceModel\Story');
	}

}