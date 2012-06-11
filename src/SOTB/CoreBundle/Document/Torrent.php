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

    private $title;
    private $name;
    private $slug;
    private $description;
    private $size; // in bytes
    private $uploader;
    private $seeders;
    private $leechers;
    private $comments;

    private $_file; // only used to pass the file in the upload form
    private $filename; // for locating the torrent file on disk (using the hash as the file name anyway)

    private $pieceLength;
    private $pieces;
    private $private;

    private $files;

    private $announceList;
    private $creationDate;
    private $comment;
    private $createdBy;
    private $encoding;

    private $hash;
    private $peers;
    private $activePeers;

    private $downloaded;
    private $lastUpdate;

    private $created;

    public function __construct()
    {
        $this->peers = new ArrayCollection();
        $this->created = new \DateTime();

        $this->size = 0;
        $this->seeders = 0;
        $this->leechers = 0;
        $this->downloaded = 0;
        $this->comments = new ArrayCollection();
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

    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setLeechers($leechers)
    {
        $this->leechers = $leechers;
    }

    public function getLeechers()
    {
        return $this->leechers;
    }

    public function setSeeders($seeders)
    {
        $this->seeders = $seeders;
    }

    public function getSeeders()
    {
        return $this->seeders;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getHumanSize($precision = 2)
    {
        $bytes = $this->size;

        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';

        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' KB';

        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' MB';

        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' GB';

        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $precision) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
    }

    public function getUploader()
    {
        return $this->uploader;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFile($file)
    {
        $this->_file = $file;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setPrivate($private)
    {
        $this->private = $private;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setPieces($pieces)
    {
        $this->pieces = $pieces;
    }

    public function getPieces()
    {
        return $this->pieces;
    }

    public function setPieceLength($pieceLength)
    {
        $this->pieceLength = $pieceLength;
    }

    public function getPieceLength()
    {
        return $this->pieceLength;
    }

    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
    }

    public function getDownloaded()
    {
        return $this->downloaded;
    }

    public function incrementDownloads()
    {
        $this->downloaded++;
    }

    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    public function getMagnet()
    {
        return sprintf('magnet:?xt=urn:btih:%2$s%1$sdn=%3$s%1$sxl=%4$d%1$str=%5$s', '&', $this->getHash(), urlencode($this->getName()), $this->getSize(), implode('&tr=', $this->getAnnounceList()));
    }
}
