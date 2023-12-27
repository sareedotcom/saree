<?php
namespace Logicrays\CodDisable\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class CategoryList implements ArrayInterface
{
    protected $categoryCollectionFactory;

    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = [];
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addAttributeToSelect('name');

        foreach ($categoryCollection as $category) {
            $options[] = [
                'label' => $category->getName()." - ".$category->getId(),
                'value' => $category->getId()
            ];
        }

        return $options;
    }
}
