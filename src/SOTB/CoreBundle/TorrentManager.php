<?php

namespace SOTB\CoreBundle;

use SOTB\CoreBundle\Document\Torrent;
use SOTB\CoreBundle\TorrentFile;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentManager
{
    private $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function upload(Torrent $torrent)
    {
        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $torrent->getFile();

        // the file property can be empty if the field is not required
        if (null === $file) {
            return;
        }

        $torrentData = new TorrentFile($file->getPathname());

        $torrent->setHash($torrentData->hash_info());

        $torrent->setAnnounceList($torrentData->announce());
        $torrent->setComment($torrentData->comment());
        $torrent->setCreatedBy($torrentData->created_by());
        $torrent->setCreationDate(new \DateTime('@' . $torrentData->creation_date()));
        $torrent->setName($torrentData->name());
        $torrent->setSize($torrentData->size());

        // these things are corrupt? encoding is off? i don't know. but it breaks mongo
        $torrent->setPieceLength($torrentData->piece_length());
        $torrent->setPieces($torrentData->getPieces());
        $torrent->setPrivate($torrentData->is_private());

        $torrent->setFiles($torrentData->offset());

        // move takes the target directory and then the target filename to move to
        $moved = $file->move($this->getUploadRootDir(), $torrent->getHash() . '.torrent');

        $torrent->setFilename($moved->getFilename());
    }

    public function getUploadRootDir()
    {
        return $this->baseDir;
    }
}
