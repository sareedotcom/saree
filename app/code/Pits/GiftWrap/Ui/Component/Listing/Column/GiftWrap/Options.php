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

namespace Pits\GiftWrap\Ui\Component\Listing\Column\GiftWrap;

use Magento\Framework\Data\OptionSourceInterface;
use Pits\GiftWrap\Helper\Data;

/**
 * Class Options
 *
 * @package Pits\GiftWrap\Ui\Component\Listing\Column\GiftWrap
 */
class Options implements OptionSourceInterface
{
    /**
     * @var Data
     */
    protected $giftWrapHelper;

    /**
     * Options constructor.
     *
     * @param Data $giftWrapHelper
     * @return void
     */
    public function __construct(Data $giftWrapHelper)
    {
        $this->giftWrapHelper = $giftWrapHelper;
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $options[] = [
            'label' => __('Gift Wrap Order'),
            'value' => '{',
        ];

        return $this->giftWrapHelper->isModuleEnabled() ? $options : [];
    }
}
