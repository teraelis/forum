<?php

namespace TerAelis\ForumBundle\LastPostService;

use \Doctrine\Bundle\DoctrineBundle\Registry;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\Post;

class LastPostService {
    protected $doctrine;
    protected $em;

    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager();
    }

    public function updateCategoryLastPost(Categorie $c) {
        echo "tentative de maj";
        $em = $this->em;
        $postRepository = $em->getRepository('TerAelisForumBundle:Post');

        // On met a jour la catégorie courante
        $lastPosts = $postRepository->getLastPost($c);
        foreach($lastPosts as $lp) {
            $categorie = $lp->getMainCategorie();
            $categorie->forceLastPost($lp);
            $em->persist($categorie);
            $em->flush();
        }
    }
}