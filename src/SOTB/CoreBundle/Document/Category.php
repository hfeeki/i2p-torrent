<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Category
{
    private $id;

    private $name;
    private $slug;
    private $torrents;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setTorrents($torrents)
    {
        $this->torrents = $torrents;
    }

    public function getTorrents()
    {
        return $this->torrents;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
