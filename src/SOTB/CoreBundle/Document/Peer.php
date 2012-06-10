<?php

namespace SOTB\CoreBundle\Document;

use SOTB\CoreBundle\Document\Torrent;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Peer
{
    private $id;

    private $torrent;
    private $hash;

    private $peerId;
    private $ip;
    private $port;
    private $complete;

    private $downloaded;
    private $uploaded;
    private $left;

    private $interval;
    private $expires;
    private $key;
    private $trackerId;

    private $created;

    public function __construct()
    {
        $this->complete = false;
        $this->created = new \DateTime();
        $this->expires = new \DateTime();
    }

    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;
    }

    public function getPeerId()
    {
        return $this->peerId;
    }

    public function isComplete()
    {
        return $this->complete;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setComplete($complete)
    {
        $this->complete = $complete;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
    }

    public function getDownloaded()
    {
        return $this->downloaded;
    }

    public function setInterval($interval)
    {
        if (null !== $interval) {
            $this->expires = new \DateTime();
            $this->expires->add(new \DateInterval('PT'.$interval.'S'));
        }

        $this->interval = $interval;
    }

    public function getInterval()
    {
        return $this->interval;
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

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function setTorrent(Torrent $torrent)
    {
        $this->hash = $torrent->getHash();
        $this->torrent = $torrent;

        if (!$torrent->getPeers()->contains($this)) {
            $torrent->addPeer($this);
        }
    }

    public function getTorrent()
    {
        return $this->torrent;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    public function getHash()
    {
        return $this->hash;
    }
}
