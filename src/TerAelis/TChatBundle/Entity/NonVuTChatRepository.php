<?php

namespace TerAelis\TChatBundle\Entity;

use Doctrine\ORM\EntityRepository;
use TerAelis\UserBundle\Entity\User;

/**
 * NonVuTChatRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NonVuTChatRepository extends EntityRepository
{
    /**
     * @param Salon $salon
     * @param User $user
     * @return NonVuTChat[]
     */
    public function nonVuBySalon(Salon $salon, User $user)
    {
        return $this->createQueryBuilder('nv')
            ->join('nv.salon', 's')
            ->join('nv.user', 'u')
            ->where('s.id = '.$salon->getId())
            ->andWhere('u.id = '.$user->getId())
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user, array $salons = array()) {
        if(count($salons) > 0) {
            $where = array();
            foreach($salons as $salon) {
                $where[] = 's.id = '.$salon->getId();
            }

            return $this->createQueryBuilder('nv')
                ->join('nv.salon', 's')
                ->join('nv.user', 'u')
                ->where('('.join(' or ', $where).') and u.id = '.$user->getId())
                ->getQuery()
                ->getResult();
        } else {
            return null;
        }
    }

    public function countByUser(User $user) {
        $query =  $this->createQueryBuilder('nv')
            ->select('COUNT(nv.id)')
            ->join('nv.user', 'u')
            ->where('u.id = '.$user->getId())
            ->groupBy('u.id');

        $result = $query->getQuery()
            ->getArrayResult();

        if(count($result) === 0) {
            $result = 0;
        } else {
            $result = $result[0][1];
        }

        return $result;
    }
}
