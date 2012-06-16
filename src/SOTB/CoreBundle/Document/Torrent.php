<?php

namespace SOTB\CoreBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\GroupSequenceProviderInterface;
use Symfony\Component\Validator\ExecutionContext;

use SOTB\CoreBundle\Document\Peer;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Torrent implements GroupSequenceProviderInterface
{
    private $id;

    private $title;
    private $slug;
    private $description;
    private $size; // in bytes
    private $uploader;
    private $seeders;
    private $leechers;
    private $comments;

    public $_file; // only used to pass the file in the upload form
    private $filename; // for locating the torrent file on disk (using the hash as the file name anyway)

    private $files; //display field only 
    private $info; // the original info hash

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

    private $requests;
    private $requestCount;

    private $categories;
    private $language;
    private $format;
    private $openTracked;
    private $visible;

    private $created;

    public function __construct()
    {
        $this->peers = new ArrayCollection();
        $this->created = new \DateTime();

        $this->size = 0;
        $this->seeders = 0;
        $this->leechers = 0;
        $this->downloaded = 0;
        $this->requestCount = 1;
        $this->openTracked = false;
        $this->visible = true;

        $this->comments = new ArrayCollection();
        $this->announceList = array();
        $this->requests = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->language = 'en';
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

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
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
        return sprintf('magnet:?xt=urn:btih:%2$s%1$sdn=%3$s%1$sxl=%4$d%1$str=%5$s', '&', $this->getHash(), urlencode($this->getTitle()), $this->getSize(), implode('&tr=', $this->getAnnounceList()));
    }

    public function setRequestCount($requestCount)
    {
        $this->requestCount = $requestCount;
    }

    public function getRequestCount()
    {
        return $this->requestCount;
    }

    public function setRequests($requests)
    {
        $this->requests = new ArrayCollection();
        $this->requestCount = 1;

        foreach ($requests as $request) {
            $this->addRequest($request);
        }
    }

    public function addRequest(Request $request)
    {
        if (!$this->requests instanceof ArrayCollection) {
            $this->requests = new ArrayCollection();
        }

        if (!$this->requests->contains($request)) {
            $this->requestCount++;
            $this->requests->add($request);
        }
    }

    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Returns which validation groups should be used for a certain state
     * of the object.
     *
     * @return array An array of validation groups
     */
    public function getGroupSequence()
    {
        $groups = array('always');

        if (!empty($this->_file)) {
            array_push($groups, 'upload');
        }

        if (!empty($this->hash)) {
            array_push($groups, 'hash');
        }

        if (empty($this->_file) && empty($this->hash)) {
            array_push($groups, 'either');
        }

        return $groups;
    }

    public function isValid(ExecutionContext $context)
    {
        // check if the name is actually a fake name
        if (empty($this->hash) && empty($this->_file)) {
            $context->addViolationAtSubPath('hash', 'You must supply either a torrent file or a hash/magnet.', array(), null);
            $context->addViolationAtSubPath('_file', 'You must supply either a torrent file or a hash/magnet.', array(), null);
        }
    }

    public function setCategories($categories)
    {
        $this->categories = new ArrayCollection();

        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory(Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setOpenTracked($openTracked)
    {
        $this->openTracked = $openTracked;
    }

    public function getOpenTracked()
    {
        return $this->openTracked;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function getVisible()
    {
        return $this->visible;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
