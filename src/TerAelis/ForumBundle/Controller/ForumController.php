<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\ForumBundle\Form\PostType;
use TerAelis\StatistiquesBundle\Entity\Stats;

class ForumController extends Controller
{
    public function homeAction() {
        $request = $this->get('request');
        $cookies = $request->cookies;

        if ($cookies->has('pole_aff')) {
            return $this->redirect($this->generateUrl(
                'taforum_forum',
                array(
                    'pole' => $cookies->get('pole_aff'),
                )
            ), 302);
        } else {
            return $this->redirect($this->generateUrl(
                'taforum_forum',
                array(
                    'pole' => 'litterature',
                )
            ), 302);
        }
    }

    public function forumAction($pole, $slug, $page = 1)
    {
        $categorie_courante = null;
        $categories = null;
        $sujets = null;
        $lvl = null;
        $path = null;

        $em = $this->get('doctrine.orm.entity_manager');

        /* Récupère les permissions de l'utilisateur */
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        if ($user != null) {
            $usrId = $user->getId();
            $perm = $permService->getPermission($usrId);
        } else {
            $perm = $permService->getPermissionDefault();
        }

        $repository = $em->getRepository('TerAelisForumBundle:Categorie');
        // On récupère la catégorie
        if ($slug == 'index' || $slug == '') {
            $idRoot = $this->container->getParameter($pole);
            $categorie_courante = $repository->findOneBy(array('root' => $idRoot), array('lft'=>'ASC'));
            $inter = $repository->findOneBy(array('root' => $this->container->getParameter('interpole')), array('lft'=>'ASC'));
        } else {
            $categorie_courante = $repository->findOneBy(array('slug' => $slug));
            if(empty($categorie_courante)) {
                throw $this->createNotFoundException('Catégorie inexistante');
            }
            $idRoot = $categorie_courante->getRoot();

            if($categorie_courante != null) {
                switch($categorie_courante->getId()) {
                    case $this->container->getParameter('litterature'):
                        return $this->redirect($this->generateUrl(
                            'taforum_forum',
                            array(
                                 'pole' => 'litterature',
                            )
                        ), 301);
                        break;
                    case $this->container->getParameter('rolisme'):
                        return $this->redirect($this->generateUrl(
                            'taforum_forum',
                            array(
                                 'pole' => 'rolisme',
                            )
                        ), 301);
                        break;
                    case $this->container->getParameter('graphisme'):
                        return $this->redirect($this->generateUrl(
                            'taforum_forum',
                            array(
                                 'pole' => 'graphisme',
                            )
                        ), 301);
                        break;
                    case $this->container->getParameter('interpole'):
                        return $this->redirect($this->generateUrl(
                            'taforum_forum',
                            array(
                                 'pole' => $pole,
                            )
                        ), 301);
                        break;
                }
            }
        }

        if ($categorie_courante == null) {
            throw $this->createNotFoundException('Categorie inexistante (slug = '.$slug.')');
        }


        list($pole_aff, $pole) = $this->getPoleAffichage($pole, $idRoot);

        if($pole !== 'interpole' && $pole !== $pole_aff) {
            return $this->redirect($this->generateUrl(
                'taforum_forum',
                array(
                     'pole' => $pole,
                     'slug' => $slug,

                )
            ), 301);
        }

        $lvl = $categorie_courante->getLvl() + 1;
        $path = $repository->getPath($categorie_courante);

        // On récupère les catégories filles
        $categories = $repository->getChildrenDirectByCategorie($categorie_courante);

        // On récupère les catégories de l'interpole si on est sur un index
        if ($slug == 'index' || $slug == '') {
            $categories_inter = $repository->getChildrenDirectByCategorie($inter);
        } else {
            $categories_inter = [];
        }

        // On récupère tous les enfants des catégories
        // On récupère les derniers posts de chaque catégorie
        $arrayCategories = [];
        $fullCategories = array_merge(
            array($categorie_courante),
            $categories,
            $categories_inter
        );
        foreach($fullCategories as $c) {
            $arrayCategories[$c->getId()] = $c;
        }

        $childrenPosts = $em->getRepository('TerAelisForumBundle:Categorie')
            ->findAllChildrenPosts(
                $fullCategories
            );

        // On indique sur chaque catégorie qu'elle est non lu
        if($user != null) {
            $nonVus = $em->getRepository('TerAelisForumBundle:NonVu')
                ->getNonVuByUserInCategories($user, $fullCategories);
            foreach ($nonVus as $nv) {
                $cId = $nv->getPost()->getMainCategorie()->getId();
                if (array_key_exists($cId, $arrayCategories)) {
                    $c = $arrayCategories[$cId];
                } else {
                    $c = $nv->getPost()->getMainCategorie();
                }
                $c->setNew(true);
                $arrayCategories[$cId] = $c;
            }
        }

        // On cherche le dernier post de chaque catégorie
        foreach($childrenPosts as $c) {
            if(!array_key_exists($c->getId(), $arrayCategories)) {
                $category = $c;
            } else {
                $category = $arrayCategories[$c->getId()];
            }
            if(isset($perm['voirCategorie'][$c->getId()]) && $perm['voirCategorie'][$c->getId()] == 1) {
                $children = $c->getChildren();
                foreach ($children as $child) {
                    $childId = $child->getId();
                    if(array_key_exists($childId, $arrayCategories)) {
                        $childLastPost = $arrayCategories[$childId]->getLastPost();
                        if (!empty($childLastPost)
                            && isset($perm['voirCategorie'][$childId])
                            && !empty($perm['voirCategorie'][$childId])
                            && $perm['voirCategorie'][$childId] == 1
                        ) {
                            $c->setLastPost($childLastPost);
                        }
                    }

                    // On met a jour le statut vu/non vu en fonction des fils
                    if($child->isNew()
                        && isset($perm['voirSujet'][$childId])
                        && !empty($perm['voirSujet'][$childId])
                        && $perm['voirSujet'][$childId] == 1
                    ) {
                        $c->setNew(true);
                    }
                }
                $arrayCategories[$c->getId()] = $category;
            }
        }

        $arrayRender = array(
            'categorie_courante' => $categorie_courante,
            'categories' => $arrayCategories,
            'pole' => $pole,
            'pole_aff' => $pole_aff,
            'lvl' => $lvl,
            'path' => $path,
            'voirCategorie' => $perm['voirCategorie'],
            'voirSujet' => $perm['voirSujet'],
            'creerSujet' => $perm['creerSujet'],
            'moderer' => $perm['moderer'],
            'page' => $page
        );

        // On récupère les sujets fils
        if ($slug != 'index' && $slug != '') {
            $repository = $em
                ->getRepository('TerAelisForumBundle:Post');

            $nbCommentByPage = $this->container->getParameter('nb_sujets');
            $sujets = $repository->getSujetsCategorie(
                $categorie_courante->getId(),
                $nbCommentByPage,
                $nbCommentByPage * ($page - 1)
            );

            // Récupération des vus/non vus
            if($user != null) {
                $sujetsId = array();
                foreach($sujets as $sujet) {
                    $sujetsId[$sujet->getId()] = $sujet;
                }

                $postNonVus = $em->getRepository('TerAelisForumBundle:NonVu')
                          ->getNonVuByPosts($user, array_keys($sujetsId));
                foreach($postNonVus as $nv) {
                    $post = $nv->getPost();
                    $post->setNew();
                    $sujetsId[$post->getId()] = $post;
                }
            } else {
                $sujetsId = &$sujets;
            }

            // Récupération des nb de vues
            $views = $this->container->get('ter_aelis_views.views')
                ->getViews(array(
                    'type'      => 'post',
                    'categorie' => $categorie_courante->getId(),
                    'unique'    => true
                ));

            $arrayRender['sujets'] = $sujetsId;
            $arrayRender['views'] = $views;

            $arrayRender['slug'] = $slug;
            $arrayRender['page'] = $page;
            $arrayRender['nbCommentPerPage'] = $nbCommentByPage;
            $arrayRender['nbCommentTotal'] = $categorie_courante->getNumberPost();
            foreach($categories as $c) {
                $arrayRender['nbCommentTotal'] -= $c->getNumberPost();
            }
        }

        $url = 'TerAelisForumBundle:Forum:';
        if ($slug == 'index' || $slug == '') {
            $url = $url.'index.html.twig';
            $arrayRender['categories_inter'] = $categories_inter;
            $arrayRender['categories_pole'] = $categories;
        } else {
            $url = $url.'categorie.html.twig';
            $arrayRender['categories'] = $categories;
        }

        $this->get('session')->set('pole_aff', $pole_aff);
        $response = $this->render($url, $arrayRender);
        $response->headers->setCookie(new Cookie('pole_aff', $pole_aff));
        return $response;
    }

    public function enLigneAction() {
        $stats = $this->getStats();

        $groups = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Group')
            ->findAllBut(array($this->container->getParameter('users.group_default')));

        $users = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User')
            ->getOnline();
        $nbUser = $users['count'];
        $invisible = 0;
        foreach($users['result'] as $u) {
            if(!($u->getVisible())) {
                $invisible++;
            }
        }

        return $this->render('TerAelisForumBundle:Forum:enLigne.html.twig', array(
            'groups' => $groups,
            'users' => $users['result'],
            'nbUser' => $nbUser,
            'invisible' => $invisible,
            'nbMessages' => $stats->getNbMessages(),
            'nbSujets' => $stats->getNbSujets(),
            'nbMembres' => $stats->getNbMembres()
        ));
    }

    public function getStats() {
        $em = $this->getDoctrine()
            ->getManager();
        $statsRepo = $em->getRepository('TerAelisStatistiquesBundle:Stats');
        if($statsRepo->needUpdate()) {
            $stats = new Stats();
            $stats->setNbMessages($this->getDoctrine()
                    ->getManager()
                    ->getRepository('TerAelisCommentBundle:Comment')
                    ->getNumber()
            );
            $stats->setNbSujets($this->getDoctrine()
                    ->getManager()
                    ->getRepository('TerAelisForumBundle:Post')
                    ->getNumber()
            );
            $stats->setLastUpdate(new \DateTime());
            $stats->setNbMembres($this->getDoctrine()
                    ->getManager()
                    ->getRepository('TerAelisUserBundle:User')
                    ->getNumber()
            );
            $em->persist($stats);
            $em->flush();
        } else {
            $stats = $statsRepo->getLastStats();
        }
        return $stats;
    }

    public function getOtherPoles($pole) {
        $otherPoles = $this->otherPoles($pole);
        $em = $this->getDoctrine()
            ->getManager();
        $res = $em->getRepository('TerAelisForumBundle:NonVu')
            ->getNbNonVu($otherPoles);

        return $this->render('TerAelisForumBundle:Forum:liensPoles.html.twig', array(
            'nonVus' => $res
        ));
    }

    public function lastAction($pole) {
        $otherPoles = $this->otherPoles($pole);

        $user = $this->getUser();
        $perm = $this->get('ter_aelis_forum.permission')->getPerm($user);
        $readCategories = array();
        foreach($perm['voirSujet'] as $cId => $value) {
            if(!empty($value) && $value == 1)
                $readCategories[] = $cId;
        }

        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Post');
        $posts = $repo->getLastPoles(
            $otherPoles,
            $this->container->getParameter("nb_ailleurs"),
            $readCategories
        );

        $comments = array();
        foreach($posts as $p) {
            if($p->getNumberComment() > 0) {
                $thread = $p->getThreads()->first();
                $c = $thread->getComments()->first();

                $comments[] = array(
                    'type' => 'comment',
                    'slug' => $p->getSlug(),
                    'title' => $p->getTitle(),
                    'balise' => $p->getBalise(),
                    'cat' => $p->getMainCategorie()->getRoot(),
                    'lastComment' => $p->getLastComment(),
                    'content' => $c,
                    'author' => $c->getAuthor()
                );
            } else {
                $comments[] = array(
                    'type' => 'post',
                    'slug' => $p->getSlug(),
                    'title' => $p->getTitle(),
                    'balise' => $p->getBalise(),
                    'cat' => $p->getMainCategorie()->getRoot(),
                    'lastComment' => $p->getLastComment(),
                    'content' => $p,
                    'authors' => $p->getAuthors()
                );
            }
        }
        return $this->render('TerAelisForumBundle:Forum:lastPostsSidebar.html.twig', array(
            'litte' => $this->container->getParameter('litterature'),
            'gfx' => $this->container->getParameter('graphisme'),
            'rp' => $this->container->getParameter('rolisme'),
            'pole' => $pole,
            'posts' => $comments
        ));
    }

    public function otherPoles($pole) {
        $otherPoles = array();
        switch($pole) {
            case 'litterature':
                $otherPoles[] = $this->container->getParameter('graphisme');
                $otherPoles[] = $this->container->getParameter('rolisme');
                break;
            case 'graphisme':
                $otherPoles[] = $this->container->getParameter('litterature');
                $otherPoles[] = $this->container->getParameter('rolisme');
                break;
            case 'rolisme':
                $otherPoles[] = $this->container->getParameter('graphisme');
                $otherPoles[] = $this->container->getParameter('litterature');
                break;
            default:
                $otherPoles[] = $this->container->getParameter('graphisme');
                $otherPoles[] = $this->container->getParameter('litterature');
                $otherPoles[] = $this->container->getParameter('rolisme');
                break;
        };
        return $otherPoles;
    }

    public function poleNonVusAction($pole) {
        $user = $this->getUser();
        $litte = 0;
        $gfx = 0;
        $rp = 0;

        $user = $this->getUser();
        $perm = $this->get('ter_aelis_forum.permission')->getPerm($user);
        $readCategories = array();
        foreach($perm['voirSujet'] as $cId => $value) {
            if(!empty($value) && $value == 1)
                $readCategories[] = $cId;
        }

        if($user != null) {
            $res = $this->get('doctrine.orm.entity_manager')
                ->getRepository('TerAelisForumBundle:NonVu')
                ->getNumberNonVu($user, $this->otherPoles($pole), $readCategories);

            foreach($res as $nb) {
                switch ($nb['id']) {
                    case $this->container->getParameter('litterature') :
                        $litte = $nb['nb'];
                        break;
                    case $this->container->getParameter('rolisme') :
                        $rp = $nb['nb'];
                        break;
                    case $this->container->getParameter('graphisme') :
                        $gfx = $nb['nb'];
                        break;
                }
            }
        }
        return $this->render('TerAelisForumBundle:Forum:lastPostsNav.html.twig', array(
            'litte' => $litte,
            'gfx' => $gfx,
            'rp' => $rp,
            'pole' => $pole
        ));
    }

    public function getPoleAffichage($pole, $idRoot) {
        $pole_aff = $pole;
        switch($idRoot) {
            case $this->container->getParameter("litterature"):
                $pole = "litterature";
                break;
            case $this->container->getParameter("graphisme"):
                $pole = "graphisme";
                break;
            case $this->container->getParameter("rolisme"):
                $pole = "rolisme";
                break;
            case $this->container->getParameter("blog"):
                $pole = "blog";
                break;
            default:
                $pole = "interpole";
        }

        return array($pole_aff, $pole);
    }
}
