<?php
namespace Lof\Gallery\Model;
class Story extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'lof_gallery_banner';

	protected $_cacheTag = 'lof_gallery_banner';

	protected $_eventPrefix = 'lof_gallery_banner';

	protected function _construct()
	{
		$this->_init('Lof\Gallery\Model\ResourceModel\Story');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}