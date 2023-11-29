<?php
/**
 * @category Mageants GiftCard
 * @package Mageants_GiftCard
 * @copyright Copyright (c) 2016 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\GiftCard\Model\Config\Source;

use Magento\Framework\Controller\ResultFactory;
use \Mageants\GiftCard\Model\Codelist;
use \Mageants\GiftCard\Model\Codeset as Modelcodeset;

/**
 * Cpdeset classs for option
 */
class Codeset extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Mageants\GiftCard\Model\Codelist
     */
    protected $codelist;
    
    /**
     * @var \Mageants\GiftCard\Model\Codeset
     */
    protected $_codeset;

    /**
     * @param Codelist $codelist
     * @param Codeset $codeset
     */
    public function __construct(
        Codelist $codelist,
        Modelcodeset $codeset
    ) {
        $this->codelist = $codelist;
        $this->_codeset = $codeset;
    }

    /**
     * Return to option
     *
     * @return Array
     */
    public function getAllOptions()
    {
        $_codelist=$this->codelist->getCollection()->addFieldToFilter('allocate', '0');
        $codesetids=[];
        foreach ($_codelist as $codelist) {
            $codesetids[]=$codelist->getCodeSetId();
        }
        $codesetArray=array_unique($codesetids);
        $options=[];
        foreach ($codesetArray as $codeid) {
            $codeset=$this->_codeset->load($codeid);
            $options[]=['value'=>$codeset->getCodeSetId(),'label'=>$codeset->getCodeTitle()];
        }
        return $options;
    }
}
