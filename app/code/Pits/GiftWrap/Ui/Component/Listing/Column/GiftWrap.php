<?php
/**
 * PIT Solutions
 *
 * NOTICE OF LICENSE
 * This source file is licenced under Webshop Extensions software license.
 * Once you have purchased the software with PIT Solutions AG or one of its
 * authorised resellers and provided that you comply with the conditions of this contract,
 * PIT Solutions AG grants you a non-exclusive license, unlimited in time for the usage of
 * the software in the manner of and for the purposes specified in the documentation according
 * to the subsequent regulations.
 *
 * @category Pits
 * @package  Pits_GiftWrap
 * @author   Pit Solutions Pvt. Ltd.
 * @copyright Copyright (c) 2021 PIT Solutions AG. (www.pitsolutions.ch)
 * @license https://www.webshopextension.com/en/licence-agreement/
 */

namespace Pits\GiftWrap\Ui\Component\Listing\Column;

use Exception;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Pits\GiftWrap\Helper\Data;
use Pits\GiftWrap\Model\GiftWrapData;
use Pits\GiftWrap\Model\Wrap;
use Psr\Log\LoggerInterface;

/**
 * Class GiftWrap
 */
class GiftWrap extends Column
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var GiftWrapData
     */
    protected $giftWrapData;

    /**
     * @var Data
     */
    protected $giftWrapHelper;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * GiftWrap constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param GiftWrapData $giftWrapData
     * @param Data $giftWrapHelper
     * @param LoggerInterface $logger
     * @param JsonSerializer $jsonSerializer
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GiftWrapData $giftWrapData,
        Data $giftWrapHelper,
        LoggerInterface $logger,
        JsonSerializer $jsonSerializer,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->logger = $logger;
        $this->giftWrapData = $giftWrapData;
        $this->giftWrapHelper = $giftWrapHelper;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        try {
            if ($this->giftWrapHelper->isModuleEnabled()) {
                if (isset($dataSource['data']['items'])) {
                    $path = $this->giftWrapData->getGiftWrapImagePath();
                    $imageHtml = '<img src="' . $path . '">';
                    foreach ($dataSource['data']['items'] as & $item) {
                        if ($item[Wrap::GIFT_WRAP_FIELD_NAME] && $this->hasGiftInOrder($item)) {
                            $item[$this->getData('name')] = $imageHtml;
                        } else {
                            $item[$this->getData('name')] = '';
                        }
                    }
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        try {
            if (!$this->giftWrapHelper->isModuleEnabled()) {
                $this->_data['config']['componentDisabled'] = true;
            }
            parent::prepare();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Check has gift in order
     *
     * @param array $item
     * @return bool
     */
    public function hasGiftInOrder(array $item)
    {
        $wrapStatus = false;
        if (!empty($item[$this->getData('name')])) {
            $orderGiftWrapData = $this->jsonSerializer->unserialize($item[$this->getData('name')]);
            if (!empty($orderGiftWrapData['whole_cart'])) {
                $wrapStatus = true;
            } else {
                foreach ($orderGiftWrapData['items'] as $wrapItem) {
                    $wrapStatus = (!empty($wrapItem)) ? true : $wrapStatus;
                }
            }
        }
        return $wrapStatus;
    }
}
