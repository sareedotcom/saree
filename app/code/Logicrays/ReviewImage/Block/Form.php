<?php
namespace Logicrays\ReviewImage\Block;

/**
 * Class Form
 * @package Logicrays\ReviewImage\Block
 */
class Form extends \Magento\Review\Block\Form
{
    /**
     * Sets Template
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Logicrays_ReviewImage::form.phtml');
    }
}
