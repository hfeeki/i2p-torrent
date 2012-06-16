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

        $announceList = array($this->announceUrl);
        // get the announce list
        $origAnnounceList = $torrentData->announce();

        // remove any non-i2p hosts
        if (is_array($origAnnounceList)) {
            foreach ($origAnnounceList as $announceUrl) {
                if ('i2p' === $this->get_tld_from_url($announceUrl) && $announceUrl !== $this->announceUrl) {
                    array_push($announceList, $announceUrl);
                }
            }
        }

        // reset the announce list
        $torrentData->announce(false);

        // add back the new list with us as primary
        $torrentData->announce($announceList);


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

    private function get_tld_from_url($url)
    {
        $tld = '';

        $url_parts = parse_url((string)$url);
        if (is_array($url_parts) && isset($url_parts['host'])) {
            $host_parts = explode('.', $url_parts['host']);
            if (is_array($host_parts) && count($host_parts) > 0) {
                $tld = array_pop($host_parts);
            }
        }

        return $tld;
    }
}
