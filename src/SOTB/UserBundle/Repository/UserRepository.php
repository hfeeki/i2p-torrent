<?php

namespace SOTB\UserBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function getEnabled()
    {
        return $this->createQueryBuilder()
            ->field('enabled')->equals(true);
    }
}