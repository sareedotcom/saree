<?php
namespace Lof\Gallery\Block;


class StoryView extends \Magento\Framework\View\Element\Template
{
    protected $_categoryfactory;
    protected $scopeConfig;
    protected $_storeManager;

    public function __construct(
        \Lof\Gallery\Model\CategoryFactory $categoryfactory,
        \Lof\Gallery\Model\StoryFactory $storyfactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_categoryfactory = $categoryfactory;
        $this->_storyfactory = $storyfactory;
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getStory(){
       $story = $this->_categoryfactory->create()->getCollection();
       return $story;
    }

    public function getStoryImages($id){
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('lof_gallery'); 
        $selectquery = "SELECT * FROM " . $table . " WHERE category_id LIKE ".$id ." LIMIT 50";
        $result = $connection->fetchAll($selectquery);
        return $result;
    }
    
    public function getBannerImage($bannerid) {
        $all_benner = $this->_storyfactory->create();
        $banner = $all_benner->load($bannerid);
        return $banner;
    }

    public function getVideoUsingId($video_id,$id) {
        if ($id==1) {
            $video = "https://www.youtube.com/embed/".$video_id."?autoplay=1";
        } else {
            $video = "https://player.vimeo.com/video/".$video_id."?autoplay=1";
        }
        return $video;
    }

    public function getSystemvalue($value) {
        return $this->scopeConfig->getValue($value);
    }

    public function getMediaUrl($image) {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $image;
    }
}