<?php

namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TerAelis\ForumBundle\Form\PermissionType;

class PermissionController extends Controller
{
    public function viewCategorieAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repositoryCategorie = $em->getRepository('TerAelisForumBundle:Categorie');
        $categorie = $repositoryCategorie->findOneBy(array('id' => $id));

        if ($categorie == null) {
            throw $this->createNotFoundException('Categorie inexistante(id = '.$id.')');
        }

        // On récupère toutes les permissions associées
        $repositoryPermission = $em->getRepository('TerAelisForumBundle:Permission');
        $permissions = $repositoryPermission->findByCategorie($id);

        return $this->render('TerAelisForumBundle:Admin:permissionsCategorie.html.twig', array(
            'categorie' => $categorie,
            'permissions' => $permissions
        ));
    }

    public function viewGroupeAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repositoryGroup = $em->getRepository('TerAelisUserBundle:Group');
        $groupe = $repositoryGroup->findOneBy(array('id' => $id));

        if ($groupe == null) {
            throw $this->createNotFoundException('Groupe inexistante(id = '.$id.')');
        }

        // On récupère toutes les permissions associées
        $repositoryPermission = $em->getRepository('TerAelisForumBundle:Permission');
        $permissions = $repositoryPermission->findByGroup($id);


        return $this->render('TerAelisForumBundle:Admin:permissionsGroupe.html.twig', array(
            'groupe' => $groupe,
            'permissions' => $permissions
        ));
    }

    public function editAction($idGroupe, $idCategorie, $type) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('TerAelisForumBundle:Permission');
        $permission = $repository->findOneByGroupeCategorie($idGroupe, $idCategorie);

        if ($permission == null) {
            throw $this->createNotFoundException('Permission inexistante(idGroupe = '.$idGroupe.', idCategorie = '.$idCategorie.')');
        }
        foreach($permission as $p) {
            print($p->getId()."<br />");
        }

        // Création du formulaire
        $form = $this->createForm(new PermissionType(), $permission);

        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();

                // On l'ajoute dans la BDD
                $em->persist($permission);
                $em->flush();

                // On informe le user que c'est bon
                $this->get('session')->getFlashBag()->add('info', 'La permission a été modifiée avec succès');

                // Puis on redirige vers la page de visualisation de cet article
                if ($type == "groupe") {
                    $url = 'admin_group_permissions';
                    $array = array("id" => $idGroupe);
                } else {
                    $url = "admin_categorie_permissions";
                    $array = array("id" => $idCategorie);
                }
                return $this->redirect( $this->generateUrl($url, $array));
            }
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Admin:editPermissions.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}