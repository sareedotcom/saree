<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Config\Source;

use Magento\Framework\Controller\ResultFactory;
use \Mageants\GiftCard\Model\Templates;

/**
 * Gift Image template class
 */
class Giftimages extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Mageants\GiftCard\Model\Templates
     */
    protected $_modelTemplates;

    /**
     * @param Templates $modelTemplates
     */
    public function __construct(
        Templates $modelTemplates
    ) {
        $this->_modelTemplates = $modelTemplates;
    }

    /**
     * Return Option set
     *
     * @return Array
     */
    public function getAllOptions()
    {
        $_templateCollection=$this->_modelTemplates->getCollection()->addFieldToFilter('status', '1');
        $options=[];
        foreach ($_templateCollection as $template) {
               $options[$template->getImageId()]=['value'=>$template->getImageId(),'label'=>$template->getImageTitle()];
        }
        return $options;
    }
}
