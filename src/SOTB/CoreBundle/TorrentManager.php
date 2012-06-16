<?php

namespace SOTB\CoreBundle;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use SOTB\CoreBundle\Document\Torrent;
use SOTB\CoreBundle\Document\Info;
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

    public function process(Torrent $torrent)
    {
        /** @var $file UploadedFile */
        $file = $torrent->_file;

        $announceList = array($this->announceUrl);

        // we only got a magnet link
        if (null === $file) {
            // let's set the announce on this anyway (was a magnet link or a hash)
            $torrent->setAnnounceList($announceList);

            return $torrent;
        }

        $torrentData = new TorrentFile($file->getPathname());

        // change some properties and write the new file
        $torrentData->created_by('Anonymous');
        //$torrentData->comment($torrentData->comment() . ('' == $torrentData->comment() ? '' : ' ') . '[anonymous]');

        // get the announce list
        $origAnnounceList = $torrentData->announce();

        // remove any non-i2p hosts
        if (is_array($origAnnounceList)) {
            foreach ($origAnnounceList as $announceUrl) {
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

        // reset the announce list
        $torrentData->announce(false);
        $torrentData->announce($announceList);

        $torrent->setHash($torrentData->hash_info());
        $torrent->setAnnounceList($torrentData->announce());
        $torrent->setComment($torrentData->comment());
        $torrent->setCreatedBy($torrentData->created_by());
        $torrent->setCreationDate(new \DateTime('@' . $torrentData->creation_date()));
        $torrent->setFiles($torrentData->offset());
        $torrent->setSize($torrentData->size());

        $info = new Info();
        $info->setName($torrentData->name());
        $info->setPieceLength($torrentData->piece_length());
        $info->setPieces($torrentData->getPieces());

        $torrentInfo = $torrentData->getInfo();

        //optional private flag
        if (array_key_exists('private', $torrentInfo)) {
            $info->setPrivate($torrentData->is_private());
        }
        if (isset($torrentInfo['files']) && is_array($torrentInfo['files'])) {
            $info->setFiles($torrentInfo['files']);
        } else {
            $info->setLength($torrentInfo['length']);

            if (isset($torrentInfo['md5sum'])) {
                $info->setMd5sum($torrentInfo['md5sum']);
            }
        }

        // add any extra fields
        foreach ($torrentInfo as $key => $val) {
            $extra = array();
            // exclude the required fields
            if (!in_array($key, array('name', 'pieces', 'piece length', 'private', 'files', 'md5sum', 'length'))) {
                $extra[$key] = $val;
            }
        }
        $info->setExtra($extra);

        $torrent->setInfo($info);

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
            'announce'      => (is_array($torrent->getAnnounceList())) ? current($torrent->getAnnounceList()) : $torrent->getAnnounceList(),
            'announce-list' => array((is_array($torrent->getAnnounceList())) ? $torrent->getAnnounceList() : array($torrent->getAnnounceList())),
            'creation date' => ($torrent->getCreationDate()) ? $torrent->getCreationDate()->getTimestamp() : time(),
            'comment'       => (null !== $torrent->getComment()) ? $torrent->getComment() : '',
            'created by'    => (null !== $torrent->getCreatedBy()) ? $torrent->getCreatedBy() : 'Anonymous',
            'info'          => $torrent->getInfo()->toArray()
        );

        ksort($data);

//        var_export($data);
//        die();

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
