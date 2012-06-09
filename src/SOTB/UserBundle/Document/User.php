<?php

namespace SOTB\UserBundle\Document;

use FOS\UserBundle\Document\User as BaseUser;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class User extends BaseUser
{
    protected $id;
    protected $firstName;
    protected $lastName;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }
}
