<?php

namespace SOTB\CoreBundle\Document;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Info
{
    private $pieceLength;
    private $pieces;
    private $name;
    private $private;

    // these are only for single file
    private $length;
    private $md5sum;

    // this is only for multi file
    private $files;

    // non-standard extra data in info hash
    private $extra;

    public function toArray()
    {
        $result = array(
            'pieces'       => $this->pieces,
            'piece length' => intval($this->pieceLength),
            'name'         => $this->name
        );

        if (null !== $this->private) {
            $result['private'] = intval($this->private);
        }

        // multi
        if (is_array($this->files)) {
            $result['files'] = $this->files;
        } else {
            //single
            $result['length'] = $this->length;
            if (!empty($this->md5sum)) {
                $result['md5sum'] = $this->md5sum;
            }
        }

        // add any extra fields
        foreach ($this->extra as $key => $val) {
            $result[$key] = $val;
        }

        // dictionary must be sorted as per spec (shouldn't the bencoder do this?)
        ksort($result);

        return $result;
    }

    public function getHash()
    {
        return sha1(bencode($this->toArray()));
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setFiles($files)
    {
        $this->files = $files;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setMd5sum($md5sum)
    {
        $this->md5sum = $md5sum;
    }

    public function getMd5sum()
    {
        return $this->md5sum;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPieceLength($pieceLength)
    {
        $this->pieceLength = $pieceLength;
    }

    public function getPieceLength()
    {
        return $this->pieceLength;
    }

    public function setPieces($pieces)
    {
        $this->pieces = $pieces;
    }

    public function getPieces()
    {
        return $this->pieces;
    }

    public function getTorrentInfo()
    {
        $info = array(

        );
    }

    public function setPrivate($private)
    {
        $this->private = $private;
    }

    public function getPrivate()
    {
        return $this->private;
    }
}
