<?php

namespace TerAelis\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TerAelis\ForumBundle\Entity\Permission;
use TerAelis\UserBundle\Entity\Group;
use TerAelis\UserBundle\Entity\User;
use TerAelis\UserBundle\Form\GroupType;

class AdminGroupController extends Controller
{
    public function indexAction()
    {
        // On récupère le sujet
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Group');

        $groups = $repository->findAll(1);

        return $this->render('TerAelisUserBundle:AdminGroup:index.html.twig', array('groups' => $groups));
    }

    public function viewAction($id) {
        // On récupère le sujet
        $em = $this->getDoctrine()
            ->getManager();

        $groupsRepository = $em->getRepository('TerAelisUserBundle:Group');
        $group = $groupsRepository->findOneBy(array('id' => $id));
        if ($group == null) {
            throw $this->createNotFoundException('Groupe inexistant (id = '.$id.')');
        }

        $usersRepository = $em->getRepository('TerAelisUserBundle:User');

        $mods = $usersRepository->findModsByGroup($id);
        $usrs = $usersRepository->findUsersByGroup($id);

        return $this->render('TerAelisUserBundle:AdminGroup:group.html.twig', array('group' => $group, 'mods' => $mods, 'users' => $usrs));
    }

    public function addAction() {
        $group = new Group("", array("ROLE_USER"));

        // Création du formulaire
        $form = $this->createForm(new GroupType(), $group);

        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {

                // On l'ajoute dans la BDD
                $em = $this->get('doctrine.orm.entity_manager');
                $em->beginTransaction();
                $em->persist($group);
                $em->flush();

                $gr = $em->getRepository('TerAelisUserBundle:Group')
                    ->findLast();
                $categories = $em->getRepository('TerAelisForumBundle:Categorie')
                    ->findAll()
                    ->getQuery()
                    ->getResult();

                // On ajoute une permission a tous les groupes
                foreach($categories as $cat) {
                    $permission = new Permission($cat, $gr);
                    $em->persist($permission);
                    $em->flush();
                }
                $em->commit();

                // On informe le user que c'est bon
                $this->get('session')->getFlashBag()->add('info', 'Le groupe a été créé avec succès');

                // Puis on redirige vers la page de visualisation de cet article
                return $this->redirect($this->generateUrl('admin_groups') );
            }
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisUserBundle:AdminGroup:param.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction(Request $request, $id) {
        // Récupération du groupe
        $repository = $this->getDoctrine()
                ->getManager()
                ->getRepository('TerAelisUserBundle:Group');
        $group = $repository->findOneBy(array('id' => $id));

        // Création du formulaire
        $form = $this->createForm(new GroupType(), $group);

        // Le sujet est déjà envoyé
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            // On l'ajoute dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($group);
            $em->flush();

            $repoUser = $em->getRepository('TerAelisUserBundle:User');
            $users = $repoUser
                ->findByChosenGroup($group);
            foreach($users as $u) {
                $u->setChosenGroup($group);
                $em->persist($u);
            }
            $em->flush();

            // On informe le user que c'est bon
            $this->get('session')->getFlashBag()->add('info', 'Le groupe a été créé avec succès');

            // Puis on redirige vers la page de visualisation de cet article
            return $this->redirect( $this->generateUrl('admin_groups') );
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisUserBundle:AdminGroup:param.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function removeAction(Request $request, $id) {
        // Récupération du groupe
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisUserBundle:Group');
        $group = $repository->findOneBy(array('id' => $id));

        // Création du formulaire
        $form = $this->createFormBuilder()
            ->getForm();

        // Le sujet est déjà envoyé
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $repoUser = $em->getRepository('TerAelisUserBundle:User');
            $users = $repoUser
                ->findByChosenGroup($group);
            foreach($users as $u) {
                $couleurGroupId = $repoUser->findNewColor($u->getId());
                $newGroup = $em->getRepository('TerAelisUserBundle:Group')
                    ->findOneBy(
                        array('id' => $couleurGroupId)
                    );
                $u->setChosenGroup($newGroup);
                $em->persist($u);
            }

            $users = $repoUser->findByGroup($group->getId());
            foreach($users as $u) {
                $u->removeGroup($group);
                $em->persist($u);
            }
            $em->flush();

            $repoPerm = $em->getRepository('TerAelisForumBundle:Permission');
            $perm = $repoPerm->findByGroup($group->getId());
            foreach($perm as $p) {
                $em->remove($p);
            }
            $em->flush();

            $em->remove($group);
            $em->flush();

            return $this->redirect( $this->generateUrl('admin_groups'));
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisUserBundle:AdminGroup:param.html.twig', array(
            'form' => $form->createView(),
            'submit' => "Supprimer le groupe ".$group->getName()
        ));
    }
}
