<?php
namespace Elsner\Productposition\Controller\Adminhtml\productposition;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class Save extends \Magento\Backend\App\Action
{
    protected $storeManager;
    protected $_productRepository;
    protected $request;
    protected $_filesystem;
    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context,\Magento\Catalog\Model\Product $productRepository,\Magento\Framework\App\Request\Http $request,StoreManagerInterface $storeManager,\Magento\Framework\Filesystem $filesystem)
    {
        $this->_productRepository = $productRepository;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
       // print_r($data);die;
        
       
       
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Elsner\Productposition\Model\Productposition');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			if (isset($_FILES['filename']) && !empty($_FILES['filename']['name']) ) {
			try{
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'filename']
				);
				$uploader->setAllowedExtensions(['csv']);
				/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
				$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
					->getDirectoryRead(DirectoryList::MEDIA);
				$result = $uploader->save($mediaDirectory->getAbsolutePath('productposition_productposition'));
					if($result['error']==0)
					{
						$data['filename'] = 'productposition_productposition' . $result['file'];
					}
                   // print_r();die;
			} catch (\Exception $e) {
				//unset($data['image']);
            }
          
        }else{
             if (isset($data['filename']) && isset($data['filename']['value'])) {
                    if (isset($data['filename']['delete'])) {
                        $data['filename'] = '';
                    } elseif (isset($data['filename']['value'])) {
                        $data['filename'] = $data['filename']['value'];
                    } else {
                        $data['filename'] = '';
                    }
                }
        }
			//var_dump($data);die;
			if(isset($data['filename']['delete']) && $data['filename']['delete'] == '1')
				$data['filename'] = '';

			
            $model->setData($data);
            if (isset($data['slider'])) {
            $a = $data['slider'];
             foreach($a as $a1){
             $model->setData("categories_ids",$a1);
        }
    }
            try {
                $model->save();
                //$path = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$model->getFilename();
                $mediapath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                $path = $mediapath . $model->getFilename();
                //echo $path;exit;
                  //-----------------------------save categorey to database-------------------------------------
            
                $requiredHeaders = array('sku', 'position');    
                $allowed =  array('csv');
                $file = fopen($path, "r");
                $firstLine = fgets($file);
                $foundHeaders = str_getcsv(trim($firstLine), ',', '"');
                $row = 0;
                $k = 0;
                if ($foundHeaders !== $requiredHeaders) {
                    $this->messageManager->addError(__('Invalid Csv File. Please download sample file.'));
                }else{
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('catalog_category_product'); 
                        $b = $model->getCategoriesIds();
                        $b1 = explode(",", $b);
                        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE){
                           $row = $row + 1; 
                            /*$product = $this->_productRepository->getIdBySku($getData[0]);*/
                            if(isset($getData[0]) && $getData[0] !== null){
                                $product = $this->_productRepository->loadByAttribute('sku',$getData[0]);
                                //echo $product->getId();exit;
                                if(empty($product) !== true && $product->getId() !== null){
                                foreach($b1 as $b2){
                                    $sql = "Update " . $tableName . " set position = ".$getData[1]." where product_id =".$product->getId()." AND category_id=".$b2;
                                    $connection->query($sql);
                                }
                              }
                            }
                        }
                     fclose($file); 
                  }
            //--------------------------------end here----------------------------------------------------
                $this->messageManager->addSuccess(__('The Productposition has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Productposition.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}