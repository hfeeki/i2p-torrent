<?php

namespace SOTB\CoreBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class PeerRepository extends DocumentRepository
{
    public function findActivePeers()
    {
        return $this->createQueryBuilder()
            ->field('complete')->notEqual(true)
            ->field('expires')->gte(new \DateTime())
            ->getQuery()
            ->execute();
    }
}