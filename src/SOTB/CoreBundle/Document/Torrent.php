<?php

namespace SOTB\CoreBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

use SOTB\CoreBundle\Document\Peer;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Torrent
{
    private $id;

    private $name;

    private $info;
    private $announceList;
    private $creationDate;
    private $comment;
    private $createdBy;
    private $encoding;

    private $hash;
    private $peers;
    private $activePeers;

    private $created;

    public function __construct()
    {
        $this->peers = new ArrayCollection();
        $this->created = new \DateTime();

        $this->info = array(
            'piece length' => 0,
            'pieces'       => 0,
            'private'      => 0,
//          //'mode' => 'single-file',
            'name'         => '',
            'length'       => 0,
            'md5sum'       => '',
//          //'mode' => 'multi-file',
//                'name' => '',
//                'files' => array(
//                    'length' => 0,
//                    'md5sum' => '',
//                    'path' => '' // bencoded
//                )
        );
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPeers($peers)
    {
        $this->peers = new ArrayCollection();

        foreach ($peers as $peer) {
            $this->addPeer($peer);
        }
    }

    public function getPeers()
    {
        return $this->peers;
    }

    public function addPeer(Peer $peer)
    {
        if (!$this->peers->contains($peer)) {
            if ($this !== $peer->getTorrent()) {
                $peer->setTorrent($this);
            }
            $this->peers->add($peer);
        }
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setAnnounceList($announceList)
    {
        $this->announceList = $announceList;
    }

    public function getAnnounceList()
    {
        return $this->announceList;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getActivePeers()
    {
        return $this->activePeers;
    }
}
