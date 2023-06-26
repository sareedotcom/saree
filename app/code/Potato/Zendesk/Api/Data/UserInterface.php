<?php

namespace Potato\Zendesk\Api\Data;

interface UserInterface
{
    const ID = 'id';
    const NAME = 'name';
    const PHOTO = 'photo';
    const ROLE = 'role';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();
    
    /**
     * @return string
     */
    public function getPhoto();

    /**
     * @return string
     */
    public function getRole();
}
