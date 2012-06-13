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
    private $announceUrl;

    public function __construct($baseDir, $announceUrl)
    {
        $this->baseDir = $baseDir;
        $this->announceUrl = $announceUrl;
    }

    public function upload(Torrent $torrent)
    {
        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $torrent->getFilename();

        // the file property can be empty if the field is not required
        if (null === $file) {
            return;
        }

        $torrentData = new TorrentFile($file->getPathname());

        // change some properties and write the new file
        $torrentData->created_by('Anonymous');
        $torrentData->comment($torrentData->comment() . ('' == $torrentData->comment() ? '' : ' ') . '[anonymous]');

        // reset the announce list
        $torrentData->announce(false);
        // use only ours
        $torrentData->announce($this->announceUrl);


        $torrent->setHash($torrentData->hash_info());
        $torrent->setAnnounceList($torrentData->announce());
        $torrent->setComment($torrentData->comment());
        $torrent->setCreatedBy($torrentData->created_by());
        $torrent->setCreationDate(new \DateTime('@' . $torrentData->creation_date()));
        $torrent->setName($torrentData->name());
        $torrent->setSize($torrentData->size());
        $torrent->setPieceLength($torrentData->piece_length());
        $torrent->setPieces($torrentData->getPieces());
        $torrent->setPrivate($torrentData->is_private());
        $torrent->setFiles($torrentData->offset());
        $torrent->setFilename($torrent->getHash() . '.torrent');

        // write a new torrent
        file_put_contents($this->getUploadRootDir() . DIRECTORY_SEPARATOR . $torrent->getFilename(), $torrentData->getFile());

        // delete the old tmp file
        if (is_file($file->getPathname())) {
            unlink($file->getPathname());
        }
    }

    public function getUploadRootDir()
    {
        return $this->baseDir;
    }
}
