<?php

namespace SOTB\CoreBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class PeerRepository extends DocumentRepository
{
    public function findActivePeers($torrent)
    {
        return $this->createQueryBuilder()
            ->field('torrent')->references($torrent)
            ->field('expires')->gte(new \DateTime())
            ->getQuery()
            ->execute();
    }
}