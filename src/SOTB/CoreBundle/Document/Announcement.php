<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Announcement
{
    private $id;
    private $infoHash;
    private $peerId;
    private $ip;
    private $port;
    private $downloaded;
    private $uploaded;
    private $left;

    private $event;

    private $interval;
    private $key;
    private $trackerId;

    private $created;

    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
        $this->created = time();
    }

    public function getDownloaded()
    {
        return $this->downloaded;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setInfoHash($infoHash)
    {
        $this->infoHash = $infoHash;
    }

    public function getInfoHash()
    {
        return $this->infoHash;
    }

    public function setInterval($interval)
    {
        $this->interval = intval($interval);
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setLeft($left)
    {
        $this->left = $left;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;
    }

    public function getPeerId()
    {
        return $this->peerId;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setTrackerId($trackerId)
    {
        $this->trackerId = $trackerId;
    }

    public function getTrackerId()
    {
        return $this->trackerId;
    }

    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    public function getUploaded()
    {
        return $this->uploaded;
    }

    public function getCreated()
    {
        return $this->created;
    }
}
