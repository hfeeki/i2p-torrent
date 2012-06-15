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

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function announce(ParameterBag $params)
    {
        if (
            !$params->has('info_hash') ||
            !$params->has('peer_id') ||
            !$params->has('port') ||
            !$params->has('uploaded') ||
            !$params->has('downloaded') ||
            !$params->has('left')
        ) {
            return $this->announceFailure("Invalid get parameters.");
        }

        // validate the request
        $info_hash = bin2hex(urldecode($params->get('info_hash')));
        if (40 != strlen($info_hash)) {
            return $this->announceFailure("Invalid length of info_hash. ". $info_hash);
        }
        $peer_id = bin2hex(urldecode($params->get('peer_id')));
        if (strlen($peer_id) < 20 || strlen($peer_id) > 128) {
            return $this->announceFailure("Invalid length of peer_id. ". $peer_id);
        }
        if (!(is_numeric($params->getInt('port')) && is_int($params->getInt('port') + 0) && 0 <= $params->getInt('port'))) {
            return $this->announceFailure("Invalid port value.");
        }
        if (!(is_numeric($params->getInt('uploaded')) && is_int($params->getInt('uploaded') + 0) && 0 <= $params->getInt('uploaded'))) {
            return $this->announceFailure("Invalid uploaded value.");
        }
        if (!(is_numeric($params->getInt('downloaded')) && is_int($params->getInt('downloaded') + 0) && 0 <= $params->getInt('downloaded'))) {
            return $this->announceFailure("Invalid downloaded value.");
        }
        if (!(is_numeric($params->getInt('left')) && is_int($params->getInt('left') + 0) && 0 <= $params->getInt('left'))) {
            return $this->announceFailure("Invalid left value.");
        }

        $torrent = $this->dm->getRepository('SOTBCoreBundle:Torrent')->findOneBy(array('hash' => $info_hash));

        if (null === $torrent) {
            // TODO: we could automatically add them and fetch the meta-data later
            return $this->announceFailure('Invalid info hash.');
        }

        $peer = $this->dm->getRepository('SOTBCoreBundle:Peer')->findOneBy(array('peerId' => $peer_id));

        if (null === $peer) {
            $peer = new Peer();
            $peer->setPeerId($peer_id);
        }

        if (0 === $params->getInt('left') || 'completed' === $params->get('event')) {
            $peer->setComplete(true);

            if ('completed' === $params->get('event')) {
                // update the torrent counter
                $torrent->incrementDownloads();
                $this->dm->persist($torrent);
            }
        }

        $peer->setTorrent($torrent);
        $peer->setIp($params->get('ip'));
        $peer->setPort($params->get('port'));
        $peer->setDownloaded($params->getInt('downloaded'));
        $peer->setUploaded($params->getInt('uploaded'));
        $peer->setLeft($params->getInt('left'));

        // todo: configurable?
        $configInterval = '900';
        $interval = $configInterval + mt_rand(round($configInterval / -10), round($configInterval / 10));

        // If the client gracefully exists, we set its ttl to 0, double-interval otherwise.
        $peer->setInterval(('stopped' === $params->get('event')) ? 0 : $interval * 2);

        $this->dm->persist($peer);

        try {
            $peers = $this->getPeers($torrent, $peer, $params->get('compact', false), $params->get('no_peer_id', false));
            $peer_stats = $this->getPeerStats($torrent, $peer);
        } catch (\Exception $e) {
            return $this->announceFailure($e->getMessage());
        }

        $response = array(
            'interval'      => $interval,
            'complete'      => intval($peer_stats['complete']),
            'incomplete'    => intval($peer_stats['incomplete']),
            'peers'         => $peers,
        );

        $this->dm->flush();

        return new TrackerResponse($response);
    }

    public function scrape(ParameterBag $params)
    {
        // todo: limit to only the requested hashes
        if ($params->has('info_hash')) {
            $info_hash = array_map(function($v)
            {
                return bin2hex($v);
            }, $params->get('info_hash'));
            $torrents = $this->dm->getRepository('SOTBCoreBundle:Torrent')->findByInfoHash($info_hash);
        } else {
            $torrents = $this->dm->getRepository('SOTBCoreBundle:Torrent')->findAll();
        }

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

    protected function getPeers(Torrent $torrent, Peer $peer, $compact = false, $no_peer_id = false)
    {
        if ($compact) {
            $return = '';
            foreach ($torrent->getActivePeers() as $aPeer) {
                if ($peer->getPeerId() !== $aPeer->getPeerId()) {
                    $return .= pack('N', ip2long($aPeer->getIp()));
                    $return .= pack('n', intval($aPeer->getPort()));
                }
            }
        } else {
            $return = array();
            foreach ($torrent->getActivePeers() as $aPeer) {
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

        return $return;
    }

    public function getPeerStats(Torrent $torrent, Peer $peer = null)
    {
        $result = array('complete' => 0, 'incomplete' => 0);

        foreach ($torrent->getActivePeers() as $aPeer) {
            if (null === $peer || $peer->getPeerId() !== $aPeer->getPeerId()) {
                if ($aPeer->isComplete()) {
                    $result['complete']++;
                } else {
                    $result['incomplete']++;
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
