<?php
// die('Bye...!');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
/*
 * Assumes doc root is set to ROOT/pub
 */
require_once 'app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
 
class AssignImages extends \Magento\Framework\App\Http implements \Magento\Framework\AppInterface
{
    public function launch()
    {
    // 	die('Bye..!');
        $state = $this->_objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('adminhtml');
 
        $ids = (isset($_REQUEST['ids'])) ? trim($_REQUEST['ids']) : '';
        if (empty($ids)) {
            echo "Please pass 'ids' parameter in request. Example: ?ids=1-10";
            die();
        }
        $attributeSetIds = array(14, 15, 16, 38);
        $fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

        $images = array();
        $images[14] = $mediaPath . 'import/size_chart/Blouse-Measurement.jpg';
        $images[15] = $mediaPath . 'import/size_chart/Salwar-Suit-Measurement.jpg';
        $images[16] = $mediaPath . 'import/size_chart/Blouse-Measurement.jpg';
        $images[38] = $mediaPath . 'import/size_chart/Mens-Measurement.jpg';

        $productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $collection = $productCollection->addAttributeToSelect('*');
        $collection->addFieldToFilter('attribute_set_id',array('in', $attributeSetIds));
        if ($ids) {
            $ids = explode("-", $ids);
            if (isset($ids[0]) && isset($ids[1])) {
                $productIds = explode(',', implode(',',range($ids[0], $ids[1])));
                $collection->addIdFilter($productIds);
            }
        }
        $collection->load();
        // echo count($collection);

        if (count($collection) > 0) {
        	foreach ($collection as $_product) {
                echo "<br>".$_product->getId();
                if ($ids) {
                    file_put_contents('uploadImage.txt', 'Range: '.$_REQUEST['ids'].PHP_EOL, FILE_APPEND);
                }
                file_put_contents('uploadImage.txt', 'ProductId: '.$_product->getId().PHP_EOL, FILE_APPEND);
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_product->getId());
                $imagePath = $images[$_product->getAttributeSetId()];
                $product->addImageToMediaGallery($imagePath, null, false, false);
                $product->save();
        	}
        }

        die('Complete..!');
    }
}
 
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('AssignImages');
$bootstrap->run($app);