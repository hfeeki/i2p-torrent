<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Request
{

    private $user;

    private $created;

    public function __construct()
    {
        $this->created = new \DateTime();
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }


}
