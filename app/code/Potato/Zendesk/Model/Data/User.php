<?php

namespace Potato\Zendesk\Model\Data;

use Potato\Zendesk\Api\Data\UserInterface;
use Magento\Framework\DataObject;

class User extends DataObject implements UserInterface
{
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(UserInterface::ID);
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->getData(UserInterface::ROLE);
    }

    /**
     * @return string
     */
    public function getPhoto()
    {
        return $this->getData(UserInterface::PHOTO);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(UserInterface::NAME);
    }
}