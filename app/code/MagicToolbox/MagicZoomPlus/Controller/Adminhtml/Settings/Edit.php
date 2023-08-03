<?php

namespace MagicToolbox\MagicZoomPlus\Controller\Adminhtml\Settings;

class Edit extends \MagicToolbox\MagicZoomPlus\Controller\Adminhtml\Settings
{
    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MagicToolbox_MagicToolbox::magictoolbox');
        $title = $resultPage->getConfig()->getTitle();
        $title->prepend('Magic Toolbox');
        $title->prepend('Magic Zoom Plus');

        $messages = $this->getUpgradeMessages();
        foreach ($messages as $message) {
            $this->messageManager->addWarning($message);
        }

        return $resultPage;
    }

    /**
     * Get upgrade messages
     *
     * @return array
     */
    public function getUpgradeMessages()
    {
        $requiredVersions = [
            'MagicToolbox_Sirv' => '3.3.4',
            'MagicToolbox_Magic360' => '1.6.8',
            'MagicToolbox_MagicZoom' => '1.6.8',
            'MagicToolbox_MagicThumb' => '1.6.8',
            'MagicToolbox_MagicScroll' => '1.6.8',
            'MagicToolbox_MagicSlideshow' => '1.6.8',
            'Sirv_Magento2' => '4.0.0',
        ];

        /** @var \MagicToolbox\MagicZoomPlus\Helper\Data $dataHelper */
        $dataHelper = $this->getDataHelper();

        $modulesData = $dataHelper->getModulesData();

        $messages = [];

        foreach ($requiredVersions as $module => $requiredVersion) {
            if (isset($modulesData[$module]) && $modulesData[$module]) {
                if (version_compare($modulesData[$module], $requiredVersion, '<')) {
                    if (in_array($module, ['MagicToolbox_Sirv', 'Sirv_Magento2'])) {
                        $downloadLink = 'https://sirv.com/help/articles/magento-cdn-sirv-extension/#installation';
                    } else {
                        $downloadLink = 'https://www.magictoolbox.com/' . strtolower(substr($module, 13)) .'/modules/magento/';
                    }
                    $messages[] = __(
                        'Your extension %1 v%2 is slightly out of date. ' .
                        'Please, update it to the latest version. ' .
                        '<a target="_blank" href="%3">Download here</a>.',
                        $module,
                        $modulesData[$module],
                        $downloadLink
                    );
                }
            }
        }

        return $messages;
    }
}
