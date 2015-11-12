<?php

namespace TerAelis\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use TerAelis\UserBundle\Entity\Rang;
use TerAelis\UserBundle\Form\RangType;

class RangController extends Controller
{
    public function listAction()
    {
        $em = $this->getDoctrine()
            ->getManager();
        $rang = $em->getRepository('TerAelisUserBundle:Rang')
            ->findAll();

        return $this->render('TerAelisUserBundle:Rang:liste.html.twig', array(
            'rang' => $rang
        ));
    }

    public function addAction() {
        $rang = new Rang();
        $form = $this->createForm(new RangType(), $rang);

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);

            if(null !== $rang->getFile()) {
                $size = getimagesize($rang->getFile());
                if($size[0] == 0 || $size[1] == 0) {
                    $form->addError(new FormError("L'avatar utilisé doit être une image."));
                } elseif($size[0] > 150 || $size[1] > 150) {
                    $form->addError(new FormError("L'avatar utilisé dépasse les dimensions maximales (150*150)."));
                }
            }

            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $rang->upload();
                $em->persist($rang);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_rangs'));
            }
        }

        return $this->render('TerAelisUserBundle:Rang:add.html.twig', array(
            'form' => $form->createView(),
            'submit' => 'Ajouter'
        ));
    }

    public function viewAction($id) {
        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        return $this->render('TerAelisUserBundle:Rang:view.html.twig', array(
            'rang' => $rang
        ));
    }

    public function editAction($id) {
        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        $form = $this->createForm(new RangType(), $rang);

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);

            if(null !== $rang->getFile()) {
                $size = getimagesize($rang->getFile());
                if($size[0] == 0 || $size[1] == 0) {
                    $form->addError(new FormError("L'avatar utilisé doit être une image."));
                } elseif($size[0] > 150 || $size[1] > 150) {
                    $form->addError(new FormError("L'avatar utilisé dépasse les dimensions maximales (150*150)."));
                }
            }

            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $rang->upload();
                $em->persist($rang);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_rangs'));
            }
        }

        return $this->render('TerAelisUserBundle:Rang:add.html.twig', array(
            'form' => $form->createView(),
            'rang' => $rang,
            'submit' => 'Editer'
        ));
    }

    public function removeAction($id) {
        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        $form = $this->createFormBuilder()
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $em->remove($rang);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_rangs'));
            }
        }

        return $this->render('TerAelisUserBundle:Rang:add.html.twig', array(
            'form' => $form->createView(),
            'rang' => $rang,
            'submit' => 'Supprimer le rang'
        ));
    }

    public function addToUserAction($id) {

        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        $form = $this->createFormBuilder()
            ->add('user', 'text', array('label' => "Pseudo de l'utilisateur"))
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);

            // On l'ajoute dans la BDD
            $em = $this->getDoctrine()->getManager();
            $repositoryUser = $em->getRepository("TerAelisUserBundle:User");
            $user = $repositoryUser->findOneByName($form["user"]->getData());

            if(empty($user)) {
                $form->addError(new FormError('Cet utilisateur n\'existe pas.'));
            }

            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $user->addRang($rang);
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_rang_view', array('id' => $id)));
            }
        }

        return $this->render('TerAelisUserBundle:Rang:add.html.twig', array(
            'form' => $form->createView(),
            'rang' => $rang,
            'submit' => 'Ajouter l\'utilisateur'
        ));
    }

    public function listUserAction($id) {
        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        $users = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User')
            ->findByRangs(array($rang));


        return $this->render('TerAelisUserBundle:Rang:listUser.html.twig', array(
            'rang' => $rang,
            'users' => $users
        ));
    }

    public function removeToUserAction($id, $idUser) {
        $rang = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:Rang')
            ->findOneBy(array('id' => $id));

        if(empty($rang)) {
            throw $this->createNotFoundException("Rang introuvable (id = ".$id.").");
        }

        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User')
            ->findOneBy(array('id' => $idUser));

        if(empty($user)) {
            throw $this->createNotFoundException("Utilisateur introuvable (id = ".$idUser.").");
        }

        $form = $this->createFormBuilder()
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $user->removeRang($rang);
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('admin_rang_listUser', array('id' => $id)));
            }
        }

        return $this->render('TerAelisUserBundle:Rang:add.html.twig', array(
            'form' => $form->createView(),
            'rang' => $rang,
            'submit' => 'Retirer le rang à '.$user->getUsername()
        ));
    }

}
