<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_Gallery
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\Gallery\Controller\Adminhtml\Category;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Lof\Gallery\Controller\Adminhtml\Category
{
        /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    /**
     * @param \Magento\Backend\App\Action\Context
     * @param \Magento\Framework\Filesystem
     * @param \Magento\Backend\Helper\Js
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Backend\Helper\Js $jsHelper,
         \Magento\Framework\Registry $coreRegistry,
         \Magento\Framework\Filesystem $filesystem
        ) {
        $this->_fileSystem = $filesystem;
        $this->jsHelper = $jsHelper;
        parent::__construct($context,$coreRegistry);
    }
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        
        $links = $this->getRequest()->getPost('links');
        $links = is_array($links) ? $links : [];
        if(!empty($links)){
            $banners = $this->jsHelper->decodeGridSerializedInput($links['banner']);
            $data['banners'] = $banners;
        }


        if ($data) {
            $id = $this->getRequest()->getParam('category_id');
            $model = $this->_objectManager->create('Lof\Gallery\Model\Category')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This Category no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryRead(DirectoryList::MEDIA);
            $mediaFolder = 'lof/category/';
            $path = $mediaDirectory->getAbsolutePath($mediaFolder);
            
            // Delete, Upload Image
            $imagePath = $mediaDirectory->getAbsolutePath($model->getImage());
            if(isset($data['thumbnail_image']['delete']) && file_exists($imagePath.$mediaFolder)){
                //unlink($imagePath.$mediaFolder);
                $data['thumbnail_image'] = '';
            }
            if(isset($data['thumbnail_image']) && is_array($data['thumbnail_image'])){
                unset($data['thumbnail_image']);
            }

            if($image = $this->uploadImage('thumbnail_image')){
                
                $data['thumbnail_image'] = $image;
            }

            // init model and set data

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the Story.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if($this->getRequest()->getParam("duplicate")){
                    unset($data['category_id']);
                    $data['identity'] = $data['identity'].time();

                    $form = $this->_objectManager->create('Lof\Gallery\Model\Category');
                    $form->setData($data);
                    try{
                        $form->save();
                        $this->messageManager->addSuccess(__('You duplicated this story.'));
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addError($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('Something went wrong while duplicating the story.'));
                    }
                }

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function uploadImage($fieldId = 'thumbnail_image')
    {                

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($_FILES[$fieldId]) && $_FILES[$fieldId]['name']!='') 
        {
            $uploader = $this->_objectManager->create(
                'Magento\Framework\File\Uploader',
                array('fileId' => $fieldId)
                );
            $path = $this->_fileSystem->getDirectoryRead(
                DirectoryList::MEDIA
                )->getAbsolutePath(
                'catalog/category/'
                );

                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
                $mediaFolder = 'lof/category/';
                try {

                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); 
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $result = $uploader->save($mediaDirectory->getAbsolutePath($mediaFolder)
                        );
                    return $mediaFolder.$result['name'];
                } catch (\Exception $e) {

                    $this->_logger->critical($e);
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['category_id' => $this->getRequest()->getParam('category_id')]);
                }
            }
            return;
        }
}
