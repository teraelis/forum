<?php

namespace TerAelis\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\UserBundle\Entity\UserRole;

class GroupController extends Controller
{
    public function getPole() {
        $request = $this->get('request');
        $cookies = $request->cookies;
        if ($cookies->has('pole'))
        {
            $pole = $cookies->get('pole');
            return $pole;
        } else {
            $cookies->set('pole', 'litterature');
            return 'litterature';
        }
    }

    public function listeAction()
    {
        // On récupère le sujet
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Group');

        $groups = $repository->findAllBut(array(intval($this->container->getParameter('users.group_default'))));

        return $this->render('TerAelisUserBundle:Group:liste.html.twig', array('groups' => $groups));
    }

    public function viewAction($id) {
        $em = $this->getDoctrine()
            ->getManager();

        /* Groupe */
        $groupsRepository = $em->getRepository('TerAelisUserBundle:Group');
        $group = $groupsRepository->findOneBy(array('id' => $id));
        if ($group == null) {
            throw $this->createNotFoundException('Groupe inexistant (id = '.$id.')');
        }

        /* Users */
        $usersRepository = $em->getRepository('TerAelisUserBundle:User');

        $mods = $usersRepository->findModsByGroup($id);
        $usrs = $usersRepository->findUsersByGroup($id);

        /* Vérification de la permission */
        $user = $this->getUser();
        $allow = false;
        if(!empty($user)) {
            if($user->hasRole('ROLE_ADMIN')) {
                $allow = true;
            } else {
                $userId = $user->getId();
                foreach ($mods as $m) {
                    if ($m->getId() == $userId) {
                        $allow = true;
                        break;
                    }
                }
            }
        }

        return $this->render('TerAelisUserBundle:Group:view.html.twig', array('group' => $group, 'mods' => $mods, 'users' => $usrs, 'allow' => $allow));
    }

    public function ordreAction($id, $sens) {
        $em = $this->getDoctrine()
            ->getManager();
        /* Groupe */
        $groupsRepository = $em->getRepository('TerAelisUserBundle:Group');
        $group = $groupsRepository->findOneBy(array('id' => $id));
        if ($group == null) {
            throw $this->createNotFoundException('Groupe inexistant (id = '.$id.')');
        }

        $group->setOrdre($group->getOrdre() + $sens);
        $em->persist($group);
        $em->flush();

        return $this->redirect($this->generateUrl('group_liste'));
    }

    public function addUserAction(Request $request, $id) {
        /* Récupération du groupe */
        $em = $this->getDoctrine()
            ->getManager();
        $groupsRepository = $em->getRepository('TerAelisUserBundle:Group');
        $group = $groupsRepository->findOneBy(array('id' => $id));
        if ($group == null) {
            throw $this->createNotFoundException('Groupe inexistant (id = '.$id.')');
        }

        /* Vérification de la permission */
        /* Modérateurs */
        $usersRepository = $em->getRepository('TerAelisUserBundle:User');
        $mods = $usersRepository->findModsByGroup($id);
        $user = $this->getUser();
        if(empty($user)) {
            throw new AccessDeniedException("Vous n'avez pas le droit de gérer les utilisateurs de ce groupe.");
        }
        if(!$user->hasRole('ROLE_ADMIN')) {
            $userId = $user->getId();
            $allow = false;
            foreach ($mods as $m) {
                if ($m->getId() == $userId) {
                    $allow = true;
                    break;
                }
            }
        } else {
            $allow = true;
        }

        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de gérer les utilisateurs de ce groupe.");
        }

        /* Ajout du groupe au rôle */
        $role = new UserRole();
        $role->setGroupe($group);

        $form = $this->createFormBuilder()
            ->add('user', $this->get('teraelis.username_form_type'))
            ->getForm();

        // Le formulaire est déjà envoyé
        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);
            if($form->isValid() && $form->isSubmitted()) {
                $user = $form->get('user')->getData();
                $role->setUser($user);
                $role->setRole("usr");
                $em->persist($role);
                $em->flush();

                $user->addGroup($group);
                $em->persist($user);
                $em->flush();

                // On informe le user que c'est bon
                $this->get('session')->getFlashBag()->add('info', "L'utilisateur a été ajouté avec succes.");

                // Puis on redirige vers la page de visualisation de cet article
                return $this->redirect( $this->generateUrl('group_view', array('id' => $id)) );
            }
        }

        return $this->render('TerAelisUserBundle:Group:add.html.twig', array(
            'group' => $group,
            'form' => $form->createView(),
            'submit' => 'Ajouter'
        ));
    }

    public function removeUserAction(Request $request, $id, $idUser) {
        /* Récupération du groupe */
        $em = $this->getDoctrine()
            ->getManager();
        $groupsRepository = $em->getRepository('TerAelisUserBundle:Group');
        $group = $groupsRepository->findOneBy(array('id' => $id));
        if ($group == null) {
            throw $this->createNotFoundException('Groupe inexistant (id = '.$id.')');
        }

        /* Vérification de la permission */
        /* Modérateurs */
        $usersRepository = $em->getRepository('TerAelisUserBundle:User');
        $mods = $usersRepository->findModsByGroup($id);
        $user = $this->getUser();
        if(empty($user)) {
            throw new AccessDeniedException("Vous n'avez pas le droit de gérer les utilisateurs de ce groupe.");
        }
        $userId = $user->getId();
        $allow = $this->isGranted('ROLE_ADMIN');
        foreach($mods as $m) {
            if ($m->getId() == $userId) {
                $allow = true;
                break;
            }
        }

        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de gérer les utilisateurs de ce groupe.");
        }

        // On récupère l'utilisateur
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisUserBundle:User');

        $user = $repository->findOneBy(array('id' => $idUser));

        if (empty($user))
        {
            throw $this->createNotFoundException('Utilisateur inexistant (id = '.$id.')');
        }

        // Création du formulaire de suppression
        $form = $this->createFormBuilder()
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $color = $group->getCouleur();
            if($user->getColor() == $color) {
                $repoUser = $em->getRepository('TerAelisUserBundle:User');
                $couleurGroupId = $repoUser->findNewColor($user->getId());
                $newGroup = $em->getRepository('TerAelisUserBundle:Group')
                    ->findOneBy(
                        array('id' => $couleurGroupId)
                    );
                $user->setChosenGroup($newGroup);
            }
            $user->removeGroup($group);

            $repoUserRole = $em->getRepository('TerAelisUserBundle:UserRole');
            $ur = $repoUserRole->findByUserGroup($user, $group);
            if(!empty($ur)) {
                $em->remove($ur);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirect( $this->generateUrl('group_view', array('id' => $id)) );
        }

        return $this->render('TerAelisUserBundle:Group:add.html.twig', array(
            'group' => $group,
            'user' => $user,
            'form' => $form->createView(),
            'submit' => "Retirer ".$user->getUsername()." du groupe ".$group->getName()
        ));
    }
}
