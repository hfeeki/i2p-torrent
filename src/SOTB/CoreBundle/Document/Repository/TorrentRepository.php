<?php

namespace SOTB\CoreBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use FOS\UserBundle\Model\UserInterface;
use SOTB\CoreBundle\Document\Category;

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
            //->field('visible')->notEqual(false)
            ->getQuery();
    }

    public function getSearch($q)
    {
        $qb = $this->createQueryBuilder();

        $qb->field('visible')->notEqual(false)
            ->addOr($qb->expr()->field('title')->equals(new \MongoRegex('/.*' . $q . '.*/i')))
            ->addOr($qb->expr()->field('hash')->equals($q))
            ->addOr($qb->expr()->field('name')->equals(new \MongoRegex('/.*' . $q . '.*/i')));


        return $qb->getQuery();
    }

    public function getForUser(UserInterface $user)
    {
        return $this->createQueryBuilder()
            ->field('uploader')->references($user)
            ->getQuery();
    }

    public function getInCategory(Category $category)
    {
        return $this->createQueryBuilder()
            ->field('visible')->notEqual(false)
            ->field('categories')->references($category)
            ->getQuery();
    }

    public function findAllNeedingUpdate()
    {
        $qb = $this->createQueryBuilder();

        $qb->addOr($qb->expr()->field('lastUpdate')->lt(new \DateTime('1 minute ago')));
        $qb->addOr($qb->expr()->field('lastUpdate')->exists(false));
        $qb->addOr($qb->expr()->field('lastUpdate')->equals(''));

        return $qb->getQuery()->execute();
    }
}