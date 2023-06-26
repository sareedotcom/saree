<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingCost
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingCost\CustomerData;

use Exception;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomerAddress;

/**
 * Class ShippingCost
 * @package Mageplaza\ShippingCost\CustomerData
 */
class ShippingCost implements SectionSourceInterface
{
    /**
     * @var CurrentCustomerAddress
     */
    private $currentCustomerAddress;

    /**
     * ShippingCost constructor.
     *
     * @param CurrentCustomerAddress $currentCustomerAddress
     */
    public function __construct(CurrentCustomerAddress $currentCustomerAddress)
    {
        $this->currentCustomerAddress = $currentCustomerAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        try {
            if (!$address = $this->currentCustomerAddress->getDefaultShippingAddress()) {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }

        return [
            'address' => [
                'country'  => $address->getCountryId(),
                'regionId' => $address->getRegionId(),
                'region'   => $address->getRegion() ? $address->getRegion()->getRegion() : null,
                'postcode' => $address->getPostcode(),
            ]
        ];
    }
}
