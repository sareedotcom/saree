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

namespace Logicrays\CustomerWallet\Model;

class CustomerWallet extends \Magento\Framework\Model\AbstractModel
{
    public const ID = 'id';
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const CUSTOMER_ID = 'customer_id';
    public const AMOUNT = 'amount';
    public const NOTE = 'note';
    public const STATUS = 'status';
    public const TRANSFER_WALLET = 'transfer_wallet';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * CMS page cache tag.
     */
    public const CACHE_TAG = 'logicrays_customerwallet';

    /**
     * @var string
     */
    protected $_cacheTag = 'logicrays_customerwallet';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'logicrays_customerwallet';

    /**
     * _construct function
     *
     * @return Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Logicrays\CustomerWallet\Model\ResourceModel\CustomerWallet::class);
    }

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * SetId function
     *
     * @param int $id
     * @return integer
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get Status.
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * SetStatus function
     *
     * @param bool $status
     * @return boolean
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Transfer Wallet.
     *
     * @return boolean
     */
    public function getTransferWallet()
    {
        return $this->getData(self::TRANSFER_WALLET);
    }

    /**
     * Set Transfer Wallet function
     *
     * @param bool $transferWallet
     * @return boolean
     */
    public function setTransferWallet($transferWallet)
    {
        return $this->setData(self::TRANSFER_WALLET, $transferWallet);
    }

    /**
     * Get Note.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->getData(self::NOTE);
    }

    /**
     * SetNote function
     *
     * @param string $note
     * @return string
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * SetFirstname function
     *
     * @param string $name
     * @return string
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * SetEmail function
     *
     * @param string $email
     * @return string
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get CustomerId.
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * SetCustomerId function
     *
     * @param string $customerId
     * @return string
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * SetAmount function
     *
     * @param int $amount
     * @return integer
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get CreatedAt.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * SetCreatedAt function
     *
     * @param string $createdAt
     * @return string
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get UpdatedAt.
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * SetUpdatedAt function
     *
     * @param string $updatedAt
     * @return string
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
