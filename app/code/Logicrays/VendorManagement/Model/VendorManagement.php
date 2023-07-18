<?php

namespace Logicrays\VendorManagement\Model;

use Logicrays\VendorManagement\Api\Data\VendorManagementInterface;
use Magento\Framework\Model\AbstractModel;

class VendorManagement extends AbstractModel implements VendorManagementInterface
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Logicrays\VendorManagement\Model\ResourceModel\VendorManagement::class);
    }
    /**
     * Get VendorId.
     *
     * @return int
     */
    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     * Set VendorId.
     *
     * @param int $VendorId
     * @return void
     */
    public function setVendorId($VendorId)
    {
        return $this->setData(self::VENDOR_ID, $VendorId);
    }

    /**
     * Get Firstname.
     *
     * @return varchar
     */
    public function getFirstname()
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * Set Firstname.
     *
     * @param varchar $firstname
     * @return void
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Get lastname.
     *
     * @return varchar
     */
    public function getLastname()
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * Set lastname.
     *
     * @param varchar $lastname
     * @return void
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Get PublishDate.
     *
     * @return varchar
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set PublishDate.
     */
    /**
     * Undocumented function
     *
     * @param varchar $email
     * @return void
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get IsActive.
     *
     * @return varchar
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * Set IsActive.
     */
    /**
     * Undocumented function
     *
     * @param varchar $phone
     * @return void
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * Set UpdateTime.
     */
    /**
     * Undocumented function
     *
     * @param varchar $street
     * @return void
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $city
     * @return void
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getState()
    {
        return $this->getData(self::STATE);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $state
     * @return void
     */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getZipcode()
    {
        return $this->getData(self::ZIPCODE);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $zipcode
     * @return void
     */
    public function setZipcode($zipcode)
    {
        return $this->setData(self::ZIPCODE, $zipcode);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $country
     * @return void
     */
    public function setCountry($country)
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getBankName()
    {
        return $this->getData(self::BANK_NAME);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $bankName
     * @return void
     */
    public function setBankName($bankName)
    {
        return $this->setData(self::BANK_NAME, $bankName);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getAccountNo()
    {
        return $this->getData(self::ACCOUNT_NO);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $accountNo
     * @return void
     */
    public function setAccountNo($accountNo)
    {
        return $this->setData(self::ACCOUNT_NO, $accountNo);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getBranchName()
    {
        return $this->getData(self::BRANCH_NAME);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $branchName
     * @return void
     */
    public function setBranchName($branchName)
    {
        return $this->setData(self::BRANCH_NAME, $branchName);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getIfscCode()
    {
        return $this->getData(self::IFSC_CODE);
    }

    /**
     * Set CreatedAt.
     */
    /**
     * Undocumented function
     *
     * @param varchar $ifscCode
     * @return void
     */
    public function setIfscCode($ifscCode)
    {
        return $this->setData(self::IFSC_CODE, $ifscCode);
    }

    /**
     * Get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        return ['status => 1', 'type' => '0'];
    }
}
