<?php
namespace Elsner\CheckOrder\Block;

class Form extends \Magento\Framework\View\Element\Template
{
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Return url for quick view order controller.
     * @return string
     */
    public function getPostActionUrl()
    {
        $url = $this->getUrl('checkorder/info');
        return $url;
    }
}
