<?php


namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TerAelis\ForumBundle\Entity\Module;
use TerAelis\ForumBundle\Form\ModuleType;

class BlogController extends Controller
{
    public function blogAction($slug) {
        $em = $this->getDoctrine()
            ->getManager();

        $repository = $em->getRepository('TerAelisForumBundle:Post');
        $sujets = $repository->getSujetsByRoot($this->container->getParameter('blog'), $this->container->getParameter('nb_sujets'));

        $arrayRender = array('sujets' => $sujets);

        if($slug == "index" || $slug == "") {
            $url = "TerAelisForumBundle:Blog:index.html.twig";
        } else {
            $url = "TerAelisForumBundle:Blog:categorie.html.twig";

        }
        return $this->render($url, $arrayRender);
    }

    public function sideBarAction() {
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em->getRepository('TerAelisForumBundle:Module');
        $modules = $repository->findAll();
        $arrayRender = array('modules' => $modules);
        return $this->render("TerAelisForumBundle:Blog:sidebar-content.html.twig", $arrayRender);
    }

    public function listModulesAction() {
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em->getRepository('TerAelisForumBundle:Module');
        $modules = $repository->findAll();
        $arrayRender = array('modules' => $modules);
        return $this->render("TerAelisForumBundle:Blog:admin-modules-list.html.twig", $arrayRender);
    }

    public function creerModuleAction() {
        // Création du module
        $module = new Module();

        // Création du formulaire
        $form = $this->createForm(new ModuleType(), $module);

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
                $em = $this->getDoctrine()->getManager();
                $em->persist($module);
                $em->flush();
                return $this->redirect( $this->generateUrl('admin_modules') );
            }
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Blog:admin-paramModule.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editModuleAction($id) {
        // Récupération du module
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisForumBundle:Module');
        $module = $repository->findOneById($id);
        if($module == null) {
            throw $this->createNotFoundException('Module inexistant (id = '.$id.')');
        }

        // Création du formulaire
        $form = $this->createForm(new ModuleType(), $module);

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
                $em->persist($module);
                $em->flush();
                return $this->redirect( $this->generateUrl('admin_modules') );
            }
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Blog:admin-paramModule.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteModuleAction($id) {
        // Récupération du module
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisForumBundle:Module');
        $module = $repository->findOneById($id);
        if($module == null) {
            throw $this->createNotFoundException('Module inexistant (id = '.$id.')');
        }

        $form = $this->createFormBuilder()->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                // On supprime le module
                $em->remove($module);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('info', 'Article bien supprimé');

                // Puis on redirige vers l'accueil
                return $this->redirect($this->generateUrl('admin_modules'));
            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('TerAelisForumBundle:Blog:admin-deleteModule.html.twig', array(
            'module' => $module,
            'form'   => $form->createView()
        ));
    }

    public function sliderAction() {
        $container = $this->container;
        $em = $this->getDoctrine()
            ->getManager();
        $repo = $em->getRepository('TerAelisForumBundle:Post');
        $sujets = $repo->getSujetsCategorie($container->getParameter('featured'), $container->getParameter('nb_slide'));
        return $this->render("TerAelisForumBundle:Blog:slider.html.twig", array('sujets' => $sujets));
    }
}