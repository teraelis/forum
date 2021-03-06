<?php

namespace TerAelis\TChatBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SalonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SalonRepository extends EntityRepository
{
    public function getRooms($user, $nb = null, $public = null, $orderByName = false) {
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.users', 'u')
            ->leftJoin('s.mods', 'm')
            ->where('u.id = :id or m.id = :id');
        if($public !== null) {
            $query = $query->andWhere('s.private = ' . ($public ? '0' : '1'));
        }
        if($orderByName) {
            $query = $query->orderBy('s.name', 'ASC');
        } else {
            $query = $query->orderBy('s.lastUpdate', 'DESC');
        }
        $query = $query->getQuery();
        if($nb != null) {
            $query = $query->setMaxResults($nb);
        }
        return $query->setParameter('id', $user)
            ->getResult();
    }

    /**
     * @param $id
     * @return Salon
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRoomFull($id) {
        return $this->createQueryBuilder('s')
            ->where('s.id = '.$id)
            ->leftJoin('s.users', 'u')
            ->addSelect('u')
            ->leftjoin('s.mods', 'mods')
            ->addSelect('mods')
            ->leftJoin('s.messages', 'm')
            ->leftJoin('m.user', 'um')
            ->addSelect('m')
            ->addSelect('um')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPrivateRoom($userId1, $userId2) {
        $query = $this->createQueryBuilder('s')
            ->where('s.private = 1')
            ->leftJoin('s.users', 'u1')
            ->andWhere('u1.id = '.$userId1)
            ->leftJoin('s.users', 'u2')
            ->andWhere('u2.id = '.$userId2)
            ->getQuery();
        return $query
            ->getOneOrNullResult();
    }
}
