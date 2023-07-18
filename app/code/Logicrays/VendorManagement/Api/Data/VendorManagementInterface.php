<?php

namespace Logicrays\VendorManagement\Api\Data;

interface VendorManagementInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    public const VENDOR_ID = 'vendor_id';
    public const FIRSTNAME = 'firstname';
    public const LASTNAME = 'lastname';
    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const STREET = 'street';
    public const CITY = 'city';
    public const STATE = 'state';
    public const ZIPCODE = 'zipcode';
    public const COUNTRY = 'country';
    public const BANK_NAME = 'bank_name';
    public const ACCOUNT_NO = 'account_no';
    public const BRANCH_NAME = 'branch_name';
    public const IFSC_CODE = 'ifsc_code';

    /**
     * Get VendorId
     *
     * @return int
     */
    public function getVendorId();

    /**
     * Set VendorId.
     *
     * @param int $VendorId
     * @return void
     */
    public function setVendorId($VendorId);

    /**
     * Get firstname.
     *
     * @return varchar
     */
    public function getFirstname();

    /**
     * Set firstname.
     *
     * @param varchar $firstname
     * @return void
     */
    public function setFirstname($firstname);

    /**
     * Get Lastname.
     *
     * @return varchar
     */
    public function getLastname();

    /**
     * Set Lastname.
     *
     * @param varchar $lastname
     * @return void
     */
    public function setLastname($lastname);

    /**
     * Get Email
     *
     * @return varchar
     */
    public function getEmail();

    /**
     * Set Email.
     *
     * @param varchar $email
     * @return void
     */
    public function setEmail($email);

    /**
     * Get Phone.
     *
     * @return varchar
     */
    public function getPhone();

    /**
     * Set Phone.
     *
     * @param varchar $phone
     * @return void
     */
    public function setPhone($phone);

    /**
     * Get Street.
     *
     * @return varchar
     */
    public function getStreet();

    /**
     * Set Street.
     *
     * @param varchar $street
     * @return void
     */
    public function setStreet($street);

    /**
     * Get City.
     *
     * @return varchar
     */
    public function getCity();

    /**
     * Set City.
     *
     * @param varchar $city
     * @return void
     */
    public function setCity($city);

    /**
     * Get State.
     *
     * @return varchar
     */
    public function getState();

    /**
     * State
     *
     * @param varchar $state
     * @return void
     */
    public function setState($state);

    /**
     * Get Zipcode.
     *
     * @return varchar
     */
    public function getZipcode();

    /**
     * Set zipcode.
     *
     * @param varchar $zipcode
     * @return void
     */
    public function setZipcode($zipcode);

    /**
     * Get Country.
     *
     * @return varchar
     */
    public function getCountry();

    /**
     * Set Country.
     *
     * @param varchar $country
     * @return void
     */
    public function setCountry($country);

    /**
     * Get BankName.
     *
     * @return varchar
     */
    public function getBankName();

    /**
     * Set bankName.
     *
     * @param varchar $bankName
     * @return void
     */
    public function setBankName($bankName);

    /**
     * Get AccountNo.
     *
     * @return varchar
     */
    public function getAccountNo();

    /**
     * Set accountNo.
     *
     * @param varchar $accountNo
     * @return void
     */
    public function setAccountNo($accountNo);

    /**
     * Get BranchName.
     *
     * @return varchar
     */
    public function getBranchName();

    /**
     * Set branchName.
     *
     * @param varchar $branchName
     * @return void
     */
    public function setBranchName($branchName);

    /**
     * Get IfscCode.
     *
     * @return varchar
     */
    public function getIfscCode();

    /**
     * Set ifscCode.
     *
     * @param varchar $ifscCode
     * @return void
     */
    public function setIfscCode($ifscCode);
}
