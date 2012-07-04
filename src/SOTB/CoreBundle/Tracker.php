<?php

namespace SOTB\CoreBundle;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

use SOTB\CoreBundle\TrackerResponse;
use SOTB\CoreBundle\Document\Peer;
use SOTB\CoreBundle\Document\Torrent;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class Tracker
{
    private $dm;
    private $configInterval;
    private $announceUrl;

    public function __construct(DocumentManager $dm, $interval, $announceUrl)
    {
        $this->dm = $dm;
        $this->configInterval = $interval;
        $this->announceUrl = $announceUrl;
    }

    public function announce($info_hash, $peer_id, ParameterBag $params)
    {
        $torrent = $this->getTorrent($info_hash);
        $peer = $this->getPeer($peer_id);

        if ('completed' === $params->get('event') || (0 === $params->getInt('left') && $params->getInt('downloaded') > 1)) {
            $peer->setComplete(true);

            if ('completed' === $params->get('event')) {
                // update the torrent counter
                $torrent->incrementDownloads();
                $this->dm->persist($torrent);
            }
        }

        // update the peer info
        $peer->setTorrent($torrent);
        $peer->setIp($params->get('ip'));
        $peer->setPort($params->get('port'));
        $peer->setDownloaded($params->get('downloaded'));
        $peer->setUploaded($params->get('uploaded'));
        $peer->setLeft($params->get('left'));

        // Some randomizing for security?
        $interval = $this->configInterval + mt_rand(round($this->configInterval / -10), round($this->configInterval / 10));

        // If the client gracefully exists, we set its ttl to 0, double-interval otherwise.
        $peer->setInterval(('stopped' === $params->get('event')) ? 0 : $interval * 2);

        try {
            $peers = $this->getPeers($torrent, $peer, $params->get('compact'), $params->get('no_peer_id'), $params->get('numwant'));
            $peer_stats = $this->getPeerStats($torrent, $peer);
        } catch (\Exception $e) {
            return $this->announceFailure($e->getMessage());
        }

        $this->dm->persist($peer);
        $this->dm->flush();

        $response = array(
            'interval'      => $interval,
            'complete'      => intval($peer_stats['complete']),
            'incomplete'    => intval($peer_stats['incomplete']),
            'peers'         => $peers,
        );

        return new TrackerResponse($response);
    }

    public function scrape($info_hash)
    {
        $torrents = $this->dm->getRepository('SOTBCoreBundle:Torrent')->findByInfoHash($info_hash);

        $result = array(
            'files' => array()
        );

        foreach ($torrents as $torrent) {
            $result['files'][pack('H*', $torrent->getHash())] = array(
                'complete'   => $torrent->getSeeders(),
                'downloaded' => $torrent->getDownloaded(),
                'incomplete' => $torrent->getLeechers(),
                'name'       => $torrent->getName()
            );
        }

        return new TrackerResponse($result);
    }

    protected function getTorrent($info_hash)
    {
        // Find the torrent
        $torrent = $this->dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('hash' => $info_hash));

        // Open tracker
        if (null === $torrent) {
            $torrent = new Torrent();
            $torrent->setTitle($info_hash);
            $torrent->setOpenTracked(true);
            $torrent->setVisible(false);
            $torrent->setHash($info_hash);

            // add our announcer so magnets will work
            $announceList = array($this->announceUrl);
            $torrent->setAnnounceList($announceList);

            $this->dm->persist($torrent);
        }

        return $torrent;
    }

    protected function getPeer($peer_id)
    {
        // Find the peer
        $peer = $this->dm->getRepository('SOTBCoreBundle:Peer')->findOneBy(array('peerId' => $peer_id));
        if (null === $peer) {
            $peer = new Peer();
            $peer->setPeerId($peer_id);
        }

        return $peer;
    }

    protected function getPeers(Torrent $torrent, Peer $peer, $compact = false, $no_peer_id = false, $numwant = 50)
    {
        $activePeers = $torrent->getActivePeers();

        if ($compact) {
            $return = '';
            if (count($activePeers)) {
                $loopy = 1;
                foreach ($activePeers as $aPeer) {
                    if ($loopy++ > $numwant) {
                        break;
                    }
                    if ($peer->getPeerId() !== $aPeer->getPeerId()) {
                        $return .= pack('N', ip2long($aPeer->getIp()));
                        $return .= pack('n', intval($aPeer->getPort()));
                    }
                }
            }
        } else {
            $return = array();
            if (count($activePeers)) {
                $loopy = 1;
                foreach ($torrent->getActivePeers() as $aPeer) {
                    if ($loopy++ > $numwant) {
                        break;
                    }
                    if ($peer->getPeerId() !== $aPeer->getPeerId()) {
                        $result = array(
                            'ip'        => $aPeer->getIp(),
                            'port'      => $aPeer->getPort(),
                        );
                        if (!$no_peer_id) {
                            $result['peer id'] = $aPeer->getPeerId();
                        }
                        $return[] = $result;
                    }
                }
            }
        }

        return $return;
    }

    public function getPeerStats(Torrent $torrent, Peer $peer = null)
    {
        $result = array('complete' => 0, 'incomplete' => 0);
        $activePeers = $torrent->getActivePeers();

        if (count($activePeers)) {
            foreach ($activePeers as $aPeer) {
                if (null === $peer || $peer->getPeerId() !== $aPeer->getPeerId()) {
                    if ($aPeer->isComplete()) {
                        $result['complete']++;
                    } else {
                        $result['incomplete']++;
                    }
                }
            }
        }

        return $result;
    }

    protected function announceFailure($msg)
    {
        return new TrackerResponse(array('failure reason' => $msg));
    }
}
