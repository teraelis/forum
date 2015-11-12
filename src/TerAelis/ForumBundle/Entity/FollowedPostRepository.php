<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * FollowedPostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FollowedPostRepository extends EntityRepository
{
    public function findShortList($id, $nb) {
        $query = $this->createQueryBuilder('f')
            ->join('f.post', 'p')
            ->join('f.user', 'u')
            ->where('u.id = :id')
            ->groupBy('p.id')
            ->addSelect('p')
            ->addSelect('u')
            ->join('p.authors', 'a')
            ->addSelect('a')
            ->orderBy('p.lastComment', 'DESC')
            ->getQuery();
        return $query->setMaxResults($nb)
            ->setParameter('id', $id)
            ->getResult();
    }
}
