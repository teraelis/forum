<?php

namespace TerAelis\CommentBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ThreadRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ThreadRepository extends EntityRepository
{
    public function getThread($id) {
        return $this->createQueryBuilder('t')
            ->join('t.post', 'p')
            ->where('p.id = '.$id)
            ->getQuery()
            ->getResult();
    }
}