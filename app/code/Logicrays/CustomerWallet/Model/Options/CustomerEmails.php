<?php
declare(strict_types=1);
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_CustomerWallet
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

namespace Logicrays\CustomerWallet\Model\Options;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerEmails implements OptionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * __construct function
     *
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
    }

    /**
     * To Option Array function
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customers = $this->getCustomers();
        $options[] = ['label' => '-- Please Select Customer --', 'value' => ''];
        foreach ($customers as $customer) {
            $options[] = [
                'label' => $customer,
                'value' => $customer,
            ];
        }
        return $options;
    }

    /**
     * Get Customers function
     *
     * @return array
     */
    public function getCustomers()
    {
        $emails = [];
        $collection = $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->load();

        foreach ($collection as $customer) {
            if ($customer->getIsActive() == '1') {
                $emails[] = $customer->getEmail();
            }
        }
        return $emails;
    }
}
