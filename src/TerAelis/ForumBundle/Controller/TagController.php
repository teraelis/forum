<?php

namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use TerAelis\ForumBundle\Entity\Tag;

class TagController extends Controller
{
    public function listeAction($pole) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('TerAelisForumBundle:Tag');
        $tags = $repo->findAll();

        return $this->render('TerAelisForumBundle:Tag:liste.html.twig', array(
            'pole' => $pole,
            'tags' => $tags
        ));
    }

    public function getAction($pole, $slug) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('TerAelisForumBundle:Tag');
        $tag = $repo->findOneBySlug($slug);

        if($tag == null) {
            throw $this->createNotFoundException("Impossible de trouver le tag (slug = ".$slug.")");
        }

        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        if ($user != null) {
            $usrId = $user->getId();
            $perm = $permService->getPermission($usrId);
        } else {
            $perm = $permService->getPermissionDefault();
        }

        $repoPost = $em->getRepository('TerAelisForumBundle:Post');
        $posts = $repoPost->findByTags(array($tag), $perm);

        return $this->render('TerAelisForumBundle:Tag:tag.html.twig', array(
            'pole' => $pole,
            'tag' => $tag,
            'posts' => $posts
        ));
    }
}
