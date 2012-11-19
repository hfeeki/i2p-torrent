<?php

namespace SOTB\CoreBundle;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use SOTB\CoreBundle\Document\Torrent;
use SOTB\CoreBundle\Document\Info;
use SOTB\CoreBundle\Torrent\NativeEncoder;
use SOTB\CoreBundle\Torrent\NativeDecoder;
use PHP\BitTorrent\Torrent as TorrentFile;

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

    public function process(Torrent $torrent)
    {
        /** @var $file UploadedFile */
        $file = $torrent->_file;

        // let's set the announce on this anyway (was a magnet link or a hash)
        $torrent->setAnnounce($this->announceUrl);

        // we only got a magnet link
        if (null === $file) {
            return $torrent;
        }

        $torrentFile = TorrentFile::createFromTorrentFile($file->getPathname(), new NativeDecoder());

        // get the announce list
        $origAnnounceList = $torrentFile->getAnnounceList();

        // remove any non-i2p hosts
        $announceList = array();
        if (is_array($origAnnounceList)) {
            foreach ($origAnnounceList as $announceUrl) {
                // it SHOULD be a nested array, but some torrents are invalid with just a single nest
                if (is_array($announceUrl)) {
                    foreach ($announceUrl as $moreNesting) {
                        if ('i2p' === $this->get_tld_from_url($moreNesting) && $moreNesting !== $this->announceUrl) {
                            array_push($announceList, $moreNesting);
                        }
                    }
                } else {
                    if ('i2p' === $this->get_tld_from_url($announceUrl) && $announceUrl !== $this->announceUrl) {
                        array_push($announceList, $announceUrl);
                    }
                }
            }
        }
        // according to spec, it should be nested
        $announceList = array($announceList);

        // reset the announce list
        $filtered = array_filter($announceList);
        if (!empty($filtered)) {
            $torrent->setAnnounceList($announceList);
        }

        $torrent->setComment($torrentFile->getComment());
        $torrent->setCreatedBy($torrentFile->getCreatedBy());
        $torrent->setCreationDate(new \DateTime('@' . $torrentFile->getCreatedAt()));
        $torrent->setFiles($torrentFile->getFileList());
        $torrent->setSize($torrentFile->getSize());

        $torrentInfo = $torrentFile->getInfo();
        $info = new Info();
        $info->setName($torrentInfo['name']);
        $info->setPieceLength($torrentInfo['piece length']);
        $info->setPieces($torrentInfo['pieces']);

        //optional private flag
        if (array_key_exists('private', $torrentInfo)) {
            $info->setPrivate($torrentInfo['private']);
        }
        if (isset($torrentInfo['files']) && is_array($torrentInfo['files'])) {
            $info->setFiles($torrentInfo['files']);
        } else {
            $info->setLength($torrentInfo['length']);

            if (isset($torrentInfo['md5sum'])) {
                $info->setMd5sum($torrentInfo['md5sum']);
            }
        }

        // add any extra info fields (one torrent had added a "source" to be included in the hash)
        foreach ($torrentInfo as $key => $val) {
            $extra = array();
            // exclude the required fields
            if (!in_array($key, array('name', 'pieces', 'piece length', 'private', 'files', 'md5sum', 'length'))) {
                $extra[$key] = $val;
            }
        }
        $info->setExtra($extra);

        $torrent->setInfo($info);

        // calculate the info hash
        $encoder = new NativeEncoder();
        $infoHash = sha1($encoder->encode($torrentInfo));
        $torrent->setHash($infoHash);

        return $torrent;
    }

    public function upload(Torrent $torrent)
    {
        /** @var $file \Symfony\Component\HttpFoundation\File\UploadedFile */
        $file = $torrent->_file;

        if (!$file instanceof UploadedFile || null === $torrent->getHash() || '' === $torrent->getHash()) {
            return false;
        }

        $torrent->setFilename($torrent->getHash() . '.torrent');

        // we save the original uploaded file with no modifications
        $file->move($this->getUploadRootDir(), $torrent->getFilename());
    }

    public function getUploadRootDir()
    {
        return $this->baseDir;
    }

    /**
     * @param Torrent $torrent
     * @return the bencoded data for a torrent file
     */
    public function getFileData(Torrent $torrent)
    {
        $data = array(
            'announce'      => $torrent->getAnnounce(),
            'announce-list' => $torrent->getAnnounceList(),
            'creation date' => ($torrent->getCreationDate()) ? $torrent->getCreationDate()->getTimestamp() : time(),
            'comment'       => (null !== $torrent->getComment()) ? $torrent->getComment() : '',
            'created by'    => (null !== $torrent->getCreatedBy()) ? $torrent->getCreatedBy() : 'Anonymous',
            'info'          => $torrent->getInfo()->toArray()
        );

        ksort($data);

        return bencode($data);
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
