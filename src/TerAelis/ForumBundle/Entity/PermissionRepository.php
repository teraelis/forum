<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PermissionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PermissionRepository extends EntityRepository
{
    public function findByCategorie($id) {
        return $this->createQueryBuilder('p')
            ->join('p.categorie', 'c')
            ->where('c.id = '.$id)
            ->getQuery()
            ->getResult();
    }

    public function findByGroup($id) {
        return $this->createQueryBuilder('p')
            ->join('p.group', 'g')
            ->join('p.categorie', 'c')
            ->addSelect('c')
            ->addSelect('g')
            ->where('g.id = '.$id)
            ->orderBy('c.root', 'ASC')
            ->addOrderBy('c.lft', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGroups($groupArray) {
        $where = "g.id = ".$groupArray[0];
        for($i = 1; $i < count($groupArray); $i++) {
            $where = $where . " or g.id = ".$groupArray[$i];
        }
        return $this->createQueryBuilder('p')
            ->join('p.group', 'g')
            ->where($where)
            ->getQuery()
            ->getResult();

    }

    public function findOneByGroupeCategorie($idGroupe, $idCategorie) {
        return $this->createQueryBuilder('p')
            ->join('p.group', 'g')
            ->join('p.categorie', 'c')
            ->where('g.id = '.$idGroupe.' and c.id = '.$idCategorie)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
