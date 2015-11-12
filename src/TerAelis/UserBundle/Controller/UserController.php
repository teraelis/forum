<?php

namespace TerAelis\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\UserBundle\Entity\FollowedPost;
use TerAelis\UserBundle\Form\UserProfileType;

class UserController extends Controller
{
    public function listeAction(Request $request) {
        $sortField = 'lastVisit';
        $requestSort = $request->get('sort');
        if (!empty($requestSort)) {
            $sortField = $requestSort;
        }
        $order = 'DESC';
        $requestOrder = $request->get('order');
        if(!empty($requestOrder)) {
            $order = ($requestOrder ? 'ASC' : 'DESC');
        }

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User');

        $users = $repository->findBy(array(), array($sortField => $order));

        return $this->render('TerAelisUserBundle:User:liste.html.twig', array(
            'users' => $users,
            'sort' => $requestSort,
            'order' => ($order == 'ASC' ? '1' : '0')
        ));
    }

    public function profileAction($id)
    {
        // On récupère l'utilisateur
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisUserBundle:User');

        $user = $repository->findOneBy(array('id' => $id));

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        return $this->render('TerAelisUserBundle:User:profile.html.twig', array('user' => $user));
    }

    public function shortListAction($pole) {
        $user = $this->getUser();
        $liste = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User')
            ->getShortList($user->getId());

        return $this->render('TerAelisUserBundle:User:shortList.html.twig', array('pole' => $pole, 'liste' => $liste));
    }

    public function unpublishedAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        if($user->getId() != $id) {
            if(!$this->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException('You cant view this user\'s unpublished messages');
            } else {
                $user = $em->getRepository('TerAelisUserBundle:User')
                    ->findOneBy(array('id' => $id));
                if(empty($user)) {
                    throw $this->createNotFoundException('Utilisateur introuvable.');
                }
            }
        }

        $repo = $em->getRepository('TerAelisForumBundle:Post');
        $posts = $repo->findUnpublishedByUser($user->getId());

        return $this->render(
            'TerAelisUserBundle:User:listeSujets.html.twig',
            array(
                'unpublished' => true,
                'sujets' => $posts,
                'litte' => $this->container->getParameter('litterature'),
                'gfx' => $this->container->getParameter('graphisme'),
                'rp' => $this->container->getParameter('rolisme'),
                'user' => $user,
            )
        );
    }

    public function listFollowAction($pole, $nb = null) {
        $user = $this->getUser();
        if($user == null) {
            throw new AccessDeniedException('Vous devez être enregistré pour accéder à vos messages surveillés.');
        }
        if($nb == null) {
            $nb = $this->container->getParameter('ter_aelis_user.nb_surveilles');
        }

        $em = $this->getDoctrine()
            ->getManager();

        $list = $em->getRepository('TerAelisForumBundle:FollowedPost')
            ->findShortList($user->getId(), $nb);

        $arrayId = array();
        foreach($list as $l) {
            $arrayId[] = $l->getPost()->getId();
        }
        $nonVus = $em->getRepository('TerAelisForumBundle:NonVu')
            ->findByArrayId($user->getId(), $arrayId);

        $nonLus = array();
        $lus = array();
        foreach($list as $l) {
            if(in_array($l->getPost()->getId(), $nonVus)) {
                $nonLus[] = $l;
            } else {
                $lus[] = $l;
            }
        }

        return $this->render('TerAelisUserBundle:User:shortFollowedList.html.twig', array(
            'pole' => $pole,
            'nonLus' => $nonLus,
            'lus' => $lus
        ));
    }

    public function editAction(Request $request, $id) {
        if($this->isGranted('ROLE_ADMIN')) {
            // On récupère l'utilisateur
            $em = $this->getDoctrine()
                ->getManager();
            $repository = $em
                ->getRepository('TerAelisUserBundle:User');

            $user = $repository->findOneBy(array('id' => $id));
        } else {
            $user = $this->getUser();
            if($user == null || $user->getId() != $id) {
                throw new AccessDeniedException("Vous n'avez pas le droit de modifier cet utilisateur.");
            }
        }

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        $form = $this->createForm(new UserProfileType(), $user);

        // Le formulaire est déjà envoyé
        $form->handleRequest($request);
        // Gestion de la validation du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            // On récupère les informations
            if(null !== $user->getFile()) {
                $size = getimagesize($user->getFile());
                if($size[0] == 0 || $size[1] == 0) {
                    $form->addError(new FormError("L'avatar utilisé doit être une image."));
                } elseif($size[0] > 150 || $size[1] > 200) {
                    $form->addError(new FormError("L'avatar utilisé dépasse les dimensions maximales (150*200)."));
                }
            }

            if($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $user->upload();

                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('user_profile', array('id' => $id)));
            }
        }

        return $this->render('TerAelisUserBundle:User:param.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function editColorAction($id) {
        // On récupère l'utilisateur
        $em = $this->getDoctrine()
            ->getManager();
        if($this->isGranted('ROLE_ADMIN')) {
            $repository = $em
                ->getRepository('TerAelisUserBundle:User');

            $user = $repository->findOneBy(array('id' => $id));
        } else {
            $user = $this->getUser();
            if($user->getId() != $id) {
                throw new AccessDeniedException("Vous n'avez pas le droit de modifier cet utilisateur.");
            }
        }

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        $groups = $user->getGroups();
        $arrayGroups = array();
//        $arrayGroups = array($this->container->getParameter('users.group_default') => "Par défaut");
        foreach($groups as $g) {
            $arrayGroups[$g->getId()] = $g->getName();
        }

        if($user->getChosenGroup() != null) {
            $formArray = array('id' => $user->getChosenGroup()->getId());
        } else {
            $formArray = array('id' => $this->container->getParameter('users.group_default'));
        }
        $form = $this->createFormBuilder($formArray)
            ->add('id', 'choice', array(
                'choices' => $arrayGroups,
                'label' => 'Groupe de préférence'
            ))
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->submit($request);
            if($form->isValid()) {
                $chosen_group = $em->getRepository('TerAelisUserBundle:Group')
                    ->findOneBy(array('id' => $form['id']->getData()));
                $user->setChosenGroup($chosen_group);
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('user_profile', array('id' => $id)));
            }
        }

        return $this->render('TerAelisUserBundle:User:changeColor.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function listSujetAction($id, $page) {
        // On récupère l'utilisateur
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisUserBundle:User');

        $user = $repository->findOneBy(array('id' => $id));

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        $nb = $this->container->getParameter('nb_sujets');

        $permService = $this->container->get('ter_aelis_forum.permission');
        $logged = $this->getUser();
        if ($logged != null) {
            $usrId = $logged->getId();
            $perm = $permService->getPermission($usrId);
        } else {
            $perm = $permService->getPermissionDefault();
        }
        $cat = array();
        foreach($perm['voirSujet'] as $k => $p) {
            if($p == 1) {
                $cat[] = $k;
            }
        }

        $sujets = $em->getRepository('TerAelisForumBundle:Post')
            ->findByUser($id, $cat, $nb, ($page - 1) * $nb);

        return $this->render('TerAelisUserBundle:User:listeSujets.html.twig', array(
            'litte' => $this->container->getParameter('litterature'),
            'gfx' => $this->container->getParameter('graphisme'),
            'rp' => $this->container->getParameter('rolisme'),
            'nbCommentPerPage' => $nb,
            'perm' => $perm,
            'user' => $user,
            'page' => $page,
            'sujets' => $sujets
        ));
    }

    public function listCommentaireAction($id, $page) {
        // On récupère l'utilisateur
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisUserBundle:User');

        $user = $repository->findOneBy(array('id' => $id));

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }
        $nb = $this->container->getParameter('nb_sujets');

        $permService = $this->container->get('ter_aelis_forum.permission');
        $logged = $this->getUser();
        if ($logged != null) {
            $usrId = $logged->getId();
            $perm = $permService->getPermission($usrId);
        } else {
            $perm = $permService->getPermissionDefault();
        }
        $cat = array();
        foreach($perm['voirSujet'] as $k => $p) {
            if($p == 1) {
                $cat[] = $k;
            }
        }

        $sujets = $em->getRepository('TerAelisCommentBundle:Comment')
            ->findByUser($id, $cat, $nb, ($page - 1) * $nb);

        return $this->render('TerAelisUserBundle:User:listCommentaires.html.twig', array(
            'litte' => $this->container->getParameter('litterature'),
            'gfx' => $this->container->getParameter('graphisme'),
            'rp' => $this->container->getParameter('rolisme'),
            'nbCommentPerPage' => $nb,
            'perm' => $perm,
            'user' => $user,
            'page' => $page,
            'sujets' => $sujets));
    }

    public function groupsAction($id) {
        $em = $this->getDoctrine()
            ->getManager();
        if($this->isGranted('ROLE_ADMIN')) {
            // On récupère l'utilisateur
            $repository = $em
                ->getRepository('TerAelisUserBundle:User');

            $user = $repository->findOneBy(array('id' => $id));
        } else {
            $user = $this->getUser();
            if($user == null || $user->getId() != $id) {
                throw new AccessDeniedException("Vous n'avez pas le droit de modifier cet utilisateur.");
            }
        }

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        $repo = $em->getRepository('TerAelisUserBundle:Group');
        $mygroups = $repo->findUsr($id);
        $listId = array();
        $listId[] = intval($this->container->getParameter('users.group_default'));
        foreach($mygroups as $g) {
            $listId[] = $g[0]->getId();
        }
        $groups = $repo->findAllBut($listId);

        return $this->render('TerAelisUserBundle:User:groups.html.twig', array(
            'mygroups' => $mygroups,
            'groups' => $groups
        ));

    }
}
