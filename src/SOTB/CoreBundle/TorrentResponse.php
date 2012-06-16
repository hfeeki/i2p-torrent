<?php

namespace SOTB\CoreBundle;

use Symfony\Component\HttpFoundation\Response;

use SOTB\CoreBundle\Document\Torrent;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentResponse extends Response
{
    private $torrentData;

    public function __construct($torrentData, $name)
    {
        $this->torrentData = $torrentData;

        parent::__construct('', 200, array(
            'Content-Type'        => 'application/x-bittorrent',
            'Content-Length'      => strlen($torrentData),
            'Content-Disposition' => 'attachment; filename="' . $name. '.torrent"'
        ));
    }

    public function sendContent()
    {
        echo $this->torrentData;

        return $this;
    }
}
