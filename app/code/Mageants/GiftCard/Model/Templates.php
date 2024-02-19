<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model;

use Magento\Framework\Exception\LocalizedException as CoreException;

/**
 * Templates Model class
 */
class Templates extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init Templates Model class
     */
    protected function _construct()
    {
        $this->_init(\Mageants\GiftCard\Model\ResourceModel\Templates::class);
    }

    /**
     * Save file to temparary Directory
     *
     * @param string $input
     */
    public function saveFileToTmpDir($input)
    {
        try {
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($this->directory_list->getPath('media')."/templates/");
                return $result['file'];

        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                  return $e->getMessage();
            }
        }
        return '';
    }
}
