<?php

namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\ForumBundle\Entity\CategorieRepository;


class VuController extends Controller
{
    public function nonLusAction($pole) {
        $user = $this->getUser();
        if ($user == null) {
            throw new AccessDeniedException("Vous ne pouvez pas accéder à cette page. Il faut être connecté.");
        }

        $user = $this->getUser();
        $perm = $this->get('ter_aelis_forum.permission')->getPerm($user);
        $readCategories = array();
        foreach($perm['voirSujet'] as $cId => $value) {
            if(!empty($value) && $value == 1)
                $readCategories[] = $cId;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $repoNonVu = $em->getRepository('TerAelisForumBundle:NonVu');
        $nonVus = $repoNonVu->findByUser($user, $readCategories);
        $lastPost = array();
        foreach($nonVus as $nv) {
            $post = $nv->getPost();
            $post->nonVu = true;
            $found = false;
            for($i = 0; $i < count($lastPost); $i++) {
                if($lastPost[$i]->getId() == $post->getId()) {
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $lastPost[] = $post;
            }
        }

        return $this->render('TerAelisForumBundle:NonVu:liste.html.twig', array(
            'pole' => $pole,
            'lastPost' => $lastPost
        ));
    }

    public function marquerVuAction($pole, $slug = null) {
        $user = $this->getUser();
        if ($user == null) {
            throw new AccessDeniedException("Vous ne pouvez pas accéder à cette page. Il faut être connecté.");
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $nonVuRepo = $em
            ->getRepository('TerAelisForumBundle:NonVu');
        if(!empty($slug)) {
            $categorie = $em->getRepository('TerAelisForumBundle:Categorie')
                ->findOneBySlug($slug);
            if($categorie == null) {
                throw $this->createNotFoundException("Impossible de trouver la catégorie");
            }
            $path = $em->getRepository('TerAelisForumBundle:Categorie')
                ->getPath($categorie);

            $nonVus = $nonVuRepo->getNonVuByUserInCategories($user, array($categorie));
        } else {
            $categorie = null;
            $path = null;

            $user = $this->getUser();
            $perm = $this->get('ter_aelis_forum.permission')->getPerm($user);
            $readCategories = array();
            foreach($perm['voirSujet'] as $cId => $value) {
                if(!empty($value) && $value == 1)
                    $readCategories[] = $cId;
            }

            $nonVus = $nonVuRepo->findByUser($user, $readCategories);
        }

        $lastPost = array();
        foreach($nonVus as $nv) {
            $post = $nv->getPost();
            $post->nonVu = true;
            $found = false;
            for($i = 0; $i < count($lastPost); $i++) {
                if($lastPost[$i]->getId() == $post->getId()) {
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $lastPost[] = $post;
            }
        }
        $count = count($lastPost);

        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                foreach($nonVus as $nv) {
                    $em->remove($nv);
                }
                $em->flush();
            }

            return $this->redirect($this->generateUrl('taforum_pole', array(
                                                                           'pole' => $pole,
                                                                      )));
        }

        return $this->render('TerAelisForumBundle:NonVu:marquer.html.twig', array(
            'pole' => $pole,
            'categorie' => $categorie,
            'count' => $count,
            'form' => $form->createView(),
            'path' => $path
        ));
    }
}