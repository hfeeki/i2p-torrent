<?php

namespace SOTB\CoreBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentRepository extends DocumentRepository
{
    // hashes may be a single string, or an array of hashes
    public function findByInfoHash(array $hashes)
    {
        return $this->createQueryBuilder()
            ->field('hash')->in($hashes)
            ->getQuery()->execute();
    }

    public function getAll()
    {
        return $this->createQueryBuilder()
            ->getQuery();
    }
}