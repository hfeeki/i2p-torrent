<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Request
{
    private $id;

    private $title;
    private $slug;

    private $description;

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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getId()
    {
        return $this->id;
    }
}
