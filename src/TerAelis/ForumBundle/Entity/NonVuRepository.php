<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\EntityRepository;
use TerAelis\UserBundle\Entity\User;

/**
 * NonVuRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NonVuRepository extends EntityRepository
{
    public function countByUser($user, $categorie = null) {
        $query =  $this->createQueryBuilder('nv')
            ->select('COUNT(nv.id)')
            ->join('nv.user', 'u')
            ->where('u.id = '.$user->getId())
            ->join('nv.post', 'p')
            ->andWhere('p.publie = 1')
            ->andWhere('p.datePublication < :today')
            ->groupBy('p.id');
        if($categorie != null) {
            $query = $query
                ->join('p.mainCategorie', 'c')
                ->andWhere('c.id = '.$categorie->getId());
        }
        return $query->getQuery()
            ->setParameter('today', new \DateTime())
            ->getSingleResult();
    }

    public function findByUser($user, $categorie = null, $light = 0) {
        $query =  $this->createQueryBuilder('nv')
            ->join('nv.user', 'u')
            ->where('u.id = '.$user->getId())
            ->join('nv.post', 'p')
            ->groupBy('p.id')
            ->andWhere('p.publie = 1')
            ->andWhere('p.datePublication < :today');
        if(!empty($categorie)) {
            if($categorie instanceof Categorie) {
                $where = 'c.id = ' . $categorie->getId();
            } else if(is_array($categorie)) {
                $where = join(' or ', array_map(function($item) { return 'c.id = '.$item; }, $categorie));
            } else {
                $where = 'c.id = '.$categorie;
            }
            $query = $query->join('p.mainCategorie', 'c')
                ->andWhere($where);
        }
        if ($light == 0) {
            $query = $query
                ->addSelect('p')
                ->addSelect('u')
                ->leftJoin('p.tags', 't')
                ->addSelect('t');
        }
        return $query->getQuery()
            ->setParameter('today', new \DateTime())
            ->getResult();
    }

    public function findByPostSlug($user, $slug) {
        $nonVus = $this->createQueryBuilder('nv')
            ->join('nv.user', 'u')
            ->where('u.id = '.$user->getId())
            ->join('nv.post', 'p')
            ->andWhere('p.slug = ?1')
            ->andWhere('p.publie = 1')
            ->addSelect('p')
            ->setParameter(1, $slug)
            ->getQuery()
            ->getResult();
        $res = null;
        foreach ($nonVus as $nv) {
            $res = $nv;
        }
        return $res;
    }

    public function getLastNonVu(User $user, $arrayCategories) {
        $where = "";
        foreach($arrayCategories as $c) {
            $where = $where.' or (c.lft >= '.$c->getLft().' and c.rgt <= '.$c->getRgt().')';
        }
        $where = substr($where, 4);
        if (strlen($where) == 0) {
            return [];
        }
        $nonVus = $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->join('p.mainCategorie', 'c')
            ->where($where)
            ->andWhere('p.publie = 1')
            ->andWhere('p.datePublication <= :today')
            ->andWhere('u.id = '.$user->getId())
            ->groupBy('p.mainCategorie')
            ->addSelect('p')
            ->addSelect('c')
            ->getQuery()
            ->setParameter('today', new \DateTime())
            ->getResult();
        return $nonVus;
    }

    public function getNonVuByPosts(User $user, $lastPostsId) {
        $where = "";
        foreach($lastPostsId as $p) {
            $where = $where.' or p.id = '.$p;
        }
        if (strlen($where) == 0) {
            return [];
        }
        $where = substr($where, 4);
        $nonVus = $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->where($where)
            ->andWhere('u.id = '.$user->getId())
            ->andWhere('p.publie = 1')
            ->andWhere('p.datePublication <= :today')
            ->setParameter('today', new \DateTime())
            ->addSelect('p')
            ->getQuery()
            ->getResult();
        return $nonVus;
    }

    public function getNonVuByUserInCategories(User $user, $arrayCategories) {
        $where = "";
        foreach($arrayCategories as $c) {
            $where = $where.' or (c.root = '.$c->getRoot().' and c.lft >= '.$c->getLft().' and c.rgt <= '.$c->getRgt().')';
        }
        if (strlen($where) == 0) {
            return [];
        }
        $where = substr($where, 4);

        $nonVus = $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->join('p.mainCategorie', 'c')
            ->where($where)
            ->andWhere('u.id = '.intval($user->getId()))
            ->andWhere('p.publie = 1')
            ->andWhere('p.datePublication <= :today')
            ->addSelect('p')
            ->addSelect('c')
            ->orderBy('c.lvl', 'DESC')
            ->getQuery()
            ->setParameter('today', new \DateTime())
            ->getResult();
        return $nonVus;
    }

    public function getNumberNonVu(User $user, $categories, $readCategories) {
        $where = "";
        foreach($categories as $c) {
            $where = $where.' or c.root = '.$c;
        }
        $where = substr($where, 4);
        if (strlen($where) == 0) {
            return [];
        }

        $whereId = join(' or ', array_map(function($value) { return 'c.id = '.$value; }, $readCategories));

        return $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->join('p.mainCategorie', 'c')
            ->where($where)
            ->andWhere('p.datePublication < :today')
            ->andWhere('p.publie = 1')
            ->andWhere('u.id = '.$user->getId())
            ->andWhere($whereId)
            ->addGroupBy('c.root')
            ->select('c.root as id')
            ->addSelect('count(p.id) as nb')
            ->getQuery()
            ->setParameter('today', new \DateTime())
            ->getArrayResult();
    }

    public function findByArrayId($user, $arrayId) {
        $where = "";
        foreach($arrayId as $id) {
            $where = $where." or p.id = ".$id;
        }
        $where = substr($where, 4);
        if (strlen($where) == 0) {
            return [];
        }

        return $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->where($where)
            ->andWhere('u.id = '.$user)
            ->andWhere('p.publie = 1')
            ->select('p.id as id')
            ->getQuery()
            ->getArrayResult();
    }

    public function findUserPost(User $user, Post $post) {
        return $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->leftJoin('nv.comment', 'c')
            ->where('p.id = '.$post->getId())
            ->andWhere('u.id = '.$user->getId())
            ->andWhere('p.publie = 1')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $postId
     * @return NonVu[]
     */
    public function findByPost($postId)
    {
        return $this->createQueryBuilder('nv')
            ->join('nv.post', 'p')
            ->join('nv.user', 'u')
            ->where('p.id = '.$postId)
            ->addSelect('u')
            ->getQuery()
            ->getResult();
    }
}