<?php

namespace SOTB\CoreBundle;

use SOTB\CoreBundle\Document\Torrent;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentUploader
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

        // TODO: rename to the torrent hash.

        // move takes the target directory and then the target filename to move to
        $moved = $file->move($this->getUploadRootDir(), $file->getClientOriginalName());

        $torrent->setFilename($moved->getFilename());
    }

    protected function getUploadRootDir()
    {
        return $this->baseDir;
    }
}
