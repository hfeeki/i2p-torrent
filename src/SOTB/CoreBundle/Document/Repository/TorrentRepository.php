<?php

namespace SOTB\CoreBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * @author Matt Drollette <matt@drollette.com>
 */
class TorrentRepository extends DocumentRepository
{
    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder()
            ->sort('name', 'ASC')
            ->getQuery()
            ->execute();
    }
}