<?php

namespace TerAelis\StatistiquesBundle\ManageViewsService;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\StatistiquesBundle\Entity\View;

class ManageViews
{
    protected $doctrine;

    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getViews($parameters = array()) {
        $res = null;
        if(!array_key_exists('type', $parameters)) {
            throw new InvalidTypeException('L\'array $parameters doit contenir le champ type');
        }
        // Si on cherche par rapport aux posts
        if($parameters['type'] == 'post') {
            $repo = $this->doctrine
                ->getManager()
                ->getRepository('TerAelisStatistiquesBundle:View');

            // Vues uniques
            if(array_key_exists('unique', $parameters) && $parameters['unique'] == 1) {
                if(array_key_exists('post', $parameters)) {
                    $res = $repo->countUniqueViewsByPost($parameters['post']);
                } elseif(array_key_exists('categorie', $parameters)) {
                    $res = $repo->countUniqueViewsByPostsInCategorie($parameters['categorie']);
                } else {
                    $res = $repo->countUniqueViewsByPosts();
                }
            } else {
                if(array_key_exists('post', $parameters)) {
                    $res = $repo->countViewsByPost($parameters['post']);
                } elseif(array_key_exists('categorie', $parameters)) {
                    $res = $repo->countViewsByPostsInCategorie($parameters['categorie']);
                } else {
                    $res = $repo->countViewsByPosts();
                }
            }
        } else if($parameters['type'] == 'categorie') {
            $repo = $this->doctrine
                ->getManager()
                ->getRepository('TerAelisStatistiquesBundle:View');

            // Vues uniques
            if(array_key_exists('unique', $parameters) && $parameters['unique'] == 1) {
                if(array_key_exists('categorie', $parameters)) {
                    $res = $repo->countUniqueViewsByCategorie($parameters['categorie']);
                } else {
                    $res = $repo->countUniqueViewsByCategories();
                }
            } else {
                if(array_key_exists('categorie', $parameters)) {
                    $res = $repo->countViewsByCategorie($parameters['categorie']);
                } else {
                    $res = $repo->countViewsByCategories();
                }
            }
        } else {
            throw new InvalidTypeException("L'array $parameters doit contenir le champ type avec la valeur 'post' ou 'categorie'");
        }
        if($res == null) {
            $views = null;
        } else {
            $views = [];
            foreach($res as $v) {
                if (!array_key_exists($v['id'], $views)) {
                    $views[$v['id']] = $v['nb'];
                } else {
                    $views[$v['id']] += $v['nb'];
                }
            }
        }

        return $views;
    }

    public function postView($ip, $categorie, $post = null) {
        $em = $this->doctrine->getManager();
        $viewRepo = $em->getRepository('TerAelisStatistiquesBundle:View');
        $view = $viewRepo->findOneByIpCategoriePost($ip, $categorie->getId(), $post->getId());
        if($view == null) {
            $view = new View($ip, $post);
        } else {
            $view->setCount($view->getCount() + 1);
        }

        $em->persist($view);
        $em->flush();
        return true;
    }

    public function deleteView($id) {
        $em = $this->doctrine->getManager();
        $viewRepo = $em->getRepository('TerAelisStatistiquesBundle:View');
        $view = $viewRepo->findOneBy(array(
            'id' => $id
        ));
        if($view != null) {
            $em->remove($view);
            $em->flush();
        }
        return true;
    }
}
