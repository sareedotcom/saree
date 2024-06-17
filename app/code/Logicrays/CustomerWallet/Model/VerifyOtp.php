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

class VerifyOtp extends \Magento\Framework\Model\AbstractModel
{
    public const ID = 'id';
    public const REQUEST_ID = 'request_id';
    public const EMAIL = 'email';
    public const OTP = 'otp';
    public const IS_VERIFIED = 'is_verified';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * CMS page cache tag.
     */
    public const CACHE_TAG = 'logicrays_verifyotp';

    /**
     * @var string
     */
    protected $_cacheTag = 'logicrays_verifyotp';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'logicrays_verifyotp';

    /**
     * _construct function
     *
     * @return Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(\Logicrays\CustomerWallet\Model\ResourceModel\VerifyOtp::class);
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
     * Get Request Id.
     *
     * @return int
     */
    public function getRequestId()
    {
        return $this->getData(self::REQUEST_ID);
    }

    /**
     * SetRequestId function
     *
     * @param int $requestId
     * @return integer
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    /**
     * Get Otp.
     *
     * @return int
     */
    public function getOtp()
    {
        return $this->getData(self::OTP);
    }

    /**
     * SetOtp function
     *
     * @param int $otp
     * @return int
     */
    public function setOtp($otp)
    {
        return $this->setData(self::OTP, $otp);
    }

    /**
     * Get Is Verified.
     *
     * @return bool
     */
    public function getIsVerified()
    {
        return $this->getData(self::IS_VERIFIED);
    }

    /**
     * SetIsVerified function
     *
     * @param bool $isVerified
     * @return bool
     */
    public function setIsVerified($isVerified)
    {
        return $this->setData(self::IS_VERIFIED, $isVerified);
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
