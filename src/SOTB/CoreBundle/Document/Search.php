<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Search
{
    private $id;
    private $user;
    private $query;
    private $numResults;
    private $created;

    public function __construct()
    {
        $this->created = new \DateTime();
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setNumResults($numResults)
    {
        $this->numResults = $numResults;
    }

    public function getNumResults()
    {
        return $this->numResults;
    }
}
