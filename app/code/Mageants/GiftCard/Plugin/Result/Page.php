<?php
namespace Mageants\GiftCard\Plugin\Result;

use Magento\Framework\App\ResponseInterface;

class Page
{
    private $context;
    private $registry;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    public function beforeRenderResult(
        \Magento\Framework\View\Result\Page $subject,
        ResponseInterface $response
    ){

    $category = $this->registry->registry('current_category');
    
    if($this->context->getRequest()->getFullActionName() == 'catalog_product_view' && $category  && $category->getName() == "Gift Card"){
        $subject->getConfig()->addBodyClass('categorypath-giftcard');
    }

    return [$response];
    }
}