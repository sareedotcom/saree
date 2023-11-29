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

namespace Pits\GiftWrap\Model\ResourceModel\Wrap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Pits\GiftWrap\Model\ResourceModel\Wrap as ResourceModel;
use Pits\GiftWrap\Model\Wrap as Model;

/**
 * Class Collection
 *
 * @package Pits\GiftWrap\Model\ResourceModel\Wrap
 */
class Collection extends AbstractCollection
{
    /**
     * Init collection
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}

