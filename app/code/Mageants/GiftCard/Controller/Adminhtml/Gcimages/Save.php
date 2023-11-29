<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\GiftCard\Controller\Adminhtml\Gcimages;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Mageants\GiftCard\Model\Templates;

/**
 * Save Image Template
 */
class Save extends Action
{
    /**
     * @var DirectoryList
     */
    public $directory_list;

    /**
     * @var Templates
     */
    public $template;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $uploaderFactory;

    /**
     * @var Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;
    
    /**
     * @var String
     */
    protected $fileId = 'image';
    /**
     * @var array
     */
    protected $allowedExtensions = ['jpg','jpeg','gif','png'];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Templates $template
     * @param DirectoryList $directory_list
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        Action\Context $context,
        Templates $template,
        DirectoryList $directory_list,
        UploaderFactory $uploaderFactory
    ) {
        $this->resultFactory=$context;
        $this->uploaderFactory = $uploaderFactory;
        $this->template = $template;
        $this->directory_list = $directory_list;
        parent::__construct($context);
    }

    /**
     * Save Template for GiftCard
     */
    public function execute()
    {
        $data=$this->getRequest()->getPostValue();
        $urlkey=$this->getRequest()->getParam('back');
        if (!$data) {
                $this->_redirect('giftcertificate/gcimages/index');
                return;
        }
        if ($data['image_title_upoad']) {
            $imagename=$this->uploadFile();
            $data['image'] = 'templates/'.$imagename;
        } else {
            $data = $this->setImage($data);
        }
        try {
            if ($data['positiontop'] > 96) {
                $data['positiontop'] = 96;
            }
            if ($data['positiontop'] < 0) {
                $data['positiontop'] = 0;
            }
            if ($data['positionleft'] > 346) {
                $data['positionleft'] = 346;
            }
            if ($data['positionleft'] < 0) {
                $data['positionleft'] = 0;
            }
                        $templateData=$this->template;
                        $templateData->setData($data);
            if (isset($data['image_id'])) {
                    $templateData->setImageId($data['image_id']);
            }
            $templateData->save();
                            $this->messageManager->addSuccess(__('Template has been successfully saved.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('giftcertificate/gcimages/index');
    }
    
    /**
     * Save Template for GiftCard
     *
     * @param array $data
     */
    public function setImage($data)
    {
        $data = $data=$this->getRequest()->getPostValue();
        if (isset($data['image']) && $data['image']['value']) {
            $img = explode("/", $data['image']['value']);
            $imagename = $img[1];
        
            if ($imagename!=null || $imagename!='') {
                if (isset($data['image']['delete'])) {
                    $data['image']='';
                } else {
                    if ($imagename !='') {
                        $data['image']="templates/".$imagename;
                    } else {
                        if (isset($data['image'])) {
                            $imagevalue=$data['image']['value'];
                            $data['image']=$imagevalue;
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Upload Image for Template
     */
    public function uploadFile()
    {
        $destinationPath = $this->getDestinationPath();
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $this->fileId])
                ->setAllowCreateFolders(true)
                ->setAllowedExtensions($this->allowedExtensions);
                
                $result=$uploader->save($destinationPath);

            if (!$result) {
                throw new LocalizedException(
                    __('File cannot be saved to path: $1', $destinationPath)
                );
            }
            return $result['file'];
            
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }

    /**
     * Return Path for destination
     *
     * @return String
     */
    public function getDestinationPath()
    {
        return $this->directory_list->getPath('media')."templates/";
    }
}
