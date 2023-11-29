<?php

namespace Wizzy\FeaturedView\Model\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Magento\Framework\Event\Observer;

class PageLoadObserver implements ObserverInterface
{
    private $request;
    private $storeGeneralConfig;
    public function __construct(
        StoreGeneralConfig $storeGeneralConfig,
        RequestInterface $request
    ) {
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getData('layout');

        $isAutocompleteEnabled = $this->storeGeneralConfig->isAutocompleteEnabled();
        $isInstantSearchEnabled = $this->storeGeneralConfig->isInstantSearchEnabled();

        if (
            $this->request->getModuleName() === "checkout"
            && $this->request->getFullActionName() === "checkout_index_index"
        ) {
            return;
        }
        if ($isAutocompleteEnabled || $isInstantSearchEnabled) {
            $layout->getUpdate()->addHandle('wizzy_featured_view');
        }
    }
}
