<?php

namespace SOTB\CoreBundle;

use Symfony\Component\HttpFoundation\Response;

use SOTB\CoreBundle\Document\Torrent;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentResponse extends Response
{
    private $baseDir;
    private $torrent;

    public function __construct(Torrent $torrent, $baseDir)
    {
        $this->baseDir = $baseDir;
        $this->torrent = $torrent;

        parent::__construct('', 200, array(
            'Content-Type'        => 'application/x-bittorrent',
            'Content-Disposition' => 'attachment; filename="' . $this->torrent->getSlug() . '.torrent"'
        ));
    }

    public function sendContent()
    {
        return readfile($this->baseDir . DIRECTORY_SEPARATOR . $this->torrent->getFilename());
    }
}
