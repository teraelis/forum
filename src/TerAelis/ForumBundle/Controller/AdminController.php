<?php

namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\Permission;
use TerAelis\ForumBundle\Entity\TypeSujet;
use TerAelis\ForumBundle\Form\CategorieType;
use TerAelis\ForumBundle\Entity\FormulaireType;
use TerAelis\ForumBundle\Form\TypeSujetType;
use Symfony\Component\Form\FormFactory;


class AdminController extends Controller
{
    public function homeAction() {
        return $this->render('TerAelisForumBundle:Admin:home.html.twig', array('pole', 'admin'));
    }

    public function categoriesAction()
    {
        $categories = null;
        // On récupère les catégories
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie');
        $categories = $repo->childrenHierarchy();

        $query = $this->getDoctrine()
            ->getManager()
            ->createQueryBuilder()
            ->select('node')
            ->from('TerAelisForumBundle:Categorie', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->getQuery()
        ;
        $tree = $query->getResult();

        return $this->render('TerAelisForumBundle:Admin:categories.html.twig', array(
            'categories' => $categories,
            'tree' => $tree
        ));
    }

    public function creerCategorieAction(Request $request) {
        $categorie_courante = new Categorie();
        $formulaireContenu = new FormulaireType();
        $formulaireContenu->setNom("Contenu");
        $formulaireContenu->setDescription("Contenu du post");
        $formulaireContenu->setCategorie($categorie_courante);
        $categorie_courante->addFormulaire($formulaireContenu);

        // Création du formulaire
        $form = $this->createForm(new CategorieType, $categorie_courante);


        // Le sujet est déjà envoyé
        $form->handleRequest($request);
        // Gestion de la validation du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            // On récupère les informations
            $image = $categorie_courante->getFile();
            if(!empty($image)) {
                $size = getimagesize($image);
                if($size[0] == 0 || $size[1] == 0) {
                    $form->addError(new FormError("L'image utilisée doit être une image."));
                } elseif($size[0] > 400 || $size[1] > 200) {
                    $form->addError(new FormError("L'image utilisé dépasse les dimensions maximales (400*200)."));
                } else {
                    $categorie_courante->upload();
                }
            }

            foreach($categorie_courante->getFormulaire() as $i => $formulaire) {
                $formulaire->setCategorie($categorie_courante);
                $formulaire->setOrdre($i);
            }

            if ($categorie_courante->getBalise() != null) {
                foreach($categorie_courante->getBalise() as $balise) {
                    $balise->setCategorie($categorie_courante);
                }
            } else {
                $categorie_courante->setBaliseObligatoire(false);
            }

            // On l'ajoute dans la BDD
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie_courante);
            $em->flush();

            $cat = $em->getRepository('TerAelisForumBundle:Categorie')
                ->findLast();
            $groups = $em->getRepository('TerAelisUserBundle:Group')
                ->findAll();

            // On crée des permissions pour chaque groupe (par défault, tout est faux
            foreach($groups as $group) {
                $permission = new Permission($cat, $group);
                $em->persist($permission);
                $em->flush();
            }

            // On informe le user que c'est bon
            $this->get('session')->getFlashBag()->add('info', 'La catégorie a été modifiée avec succès');

            // Puis on redirige vers la page de visualisation de cet article
            return $this->redirect( $this->generateUrl('admin_home') );
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Admin:param.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function paramCategorieAction(Request $request, $slug)
    {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie');

        // On récupère la catégorie
        $categorie_courante = $repository->findOneBy(array('slug' => $slug));

        if ($categorie_courante == null) {
            throw $this->createNotFoundException('Categorie inexistante (slug = '.$slug.')');
        }

        // On récupère tous les formulaires courants
        $originalFormulaire = [];
        foreach($categorie_courante->getFormulaire() as $key => $formu) {
            $originalFormulaire[$key] = $formu;
        }

        // Création du formulaire
        $form = $this->createForm(new CategorieType, $categorie_courante);

        // Le sujet est déjà envoyé
        $form->handleRequest($request);
        // Gestion de la validation du formulaire
        if ($form->isValid())
        {
            $em = $this->getDoctrine()->getManager();

            // On récupère les informations
            $image = $categorie_courante->getFile();
            if(!empty($image)) {
                $size = getimagesize($image);
                if($size[0] == 0 || $size[1] == 0) {
                    $form->addError(new FormError("L'image utilisée doit être une image."));
                } elseif($size[0] > 400 || $size[1] > 200) {
                    $form->addError(new FormError("L'image utilisé dépasse les dimensions maximales (400*200)."));
                } else {
                    $categorie_courante->upload();
                }
            }

            // On parcours les formulaires
            foreach($categorie_courante->getFormulaire() as $i => $formulaire) {
                $formulaire->setCategorie($categorie_courante);
                $formulaire->setOrdre($i);
                // On retire les formulaires qui restent de la liste des formulaires a supprimer
                foreach($originalFormulaire as $j => $original) {
                    if ($formulaire->getId() === $original->getId()) {
                        unset($originalFormulaire[$j]);
                    }
                }
            }

            foreach($categorie_courante->getBalise() as $balise) {
                $balise->setCategorie($categorie_courante);
            }

            // On supprime tous les formulaires inutiles
            foreach($originalFormulaire as $i => $formu) {
                $categorie_courante->removeFormulaire($formu);
                $em->remove($formu);
            }

            // On l'ajoute dans la BDD
            $em->persist($categorie_courante);
            $em->flush();

            // On informe le user que c'est bon
            $this->get('session')->getFlashBag()->add('info', 'La catégorie a été modifiée avec succès');

            // Puis on redirige vers la page de visualisation de cet article
            return $this->redirect( $this->generateUrl('admin_categories') );
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Admin:param.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function categoryAction($slug, $action)
    {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie');

        // On récupère la catégorie
        $categorie_courante = $repository->findOneBy(array('slug' => $slug));

        if ($categorie_courante == null) {
            throw $this->createNotFoundException('Categorie inexistante (slug = '.$slug.')');
        }

        if ($action === 'up') {
            $repository->moveUp($categorie_courante, true);
        } else {
            $repository->moveDown($categorie_courante, true);
        }

        // On l'ajoute dans la BDD
        $em = $this->getDoctrine()->getManager();
        $em->persist($categorie_courante);
        $em->flush();

        // Puis on redirige vers la page de visualisation de cet article
        return $this->redirect( $this->generateUrl('admin_categories') );
    }

    public function supprimerAction($slug)
    {
        $form = $this->createFormBuilder()->getForm();

        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie');

        // On récupère la catégorie
        $categorie_courante = $repository->findOneBy(array('slug' => $slug));

        if ($categorie_courante == null) {
            throw $this->createNotFoundException('Categorie inexistante (slug = '.$slug.')');
        }

        // On supprime tous les posts
        $postNumber = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Post')
            ->countSujetsMainCategorie($categorie_courante->getId());


        if($postNumber > 0) {
            throw new Exception("Opération impossible : la catégorie n'est pas vide. Il faut d'abord supprimer tous les sujets de la catégorie.");
        }

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                // On supprime la catégorie
                $repository->removeFromTree($categorie_courante);
                $em->remove($categorie_courante);
                $em->flush();

                // On définit un message flash
                $this->get('session')->getFlashBag()->add('info', 'Catégorie bien supprimée');

                // Puis on redirige vers l'accueil
                return $this->redirect($this->generateUrl('admin_categories'));
            }
        }

        // Si la requête est en GET, on affiche une page de confirmation avant de supprimer
        return $this->render('TerAelisForumBundle:Admin:supprimer.html.twig', array(
            'categorie_courante' => $categorie_courante,
            'form'    => $form->createView()
        ));
    }

    public function typeSujetCreerAction(Request $request) {
        $type = new TypeSujet();
        $form = $this->createForm(new TypeSujetType(), $type);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('TerAelisForumBundle:TypeSujet');
            $count = $repo->getOrdreMax();
            $type->setOrdre($count+1);
            $em->persist($type);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_typeSujet'));
        }

        return $this->render('TerAelisForumBundle:Admin:typeSujetCreer.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function typesSujetAction() {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:TypeSujet');

        $types = $repository->createQueryBuilder('ts')
            ->orderBy('ts.ordre', 'DESC')
            ->getQuery()
            ->getResult();

        // Création du formulaire
        $form = [];
        $type_objet = [];
        foreach($types as $i => $type) {
            $type_objet[$i] = $type;
            $form[$i] = $this->get('form.factory')
                ->createNamed('typeteraelis_forumbundle_typesujettype'.$i, new TypeSujetType(), $type);
        }

        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() === 'POST')
        {
            // On récupère les informations
            foreach($form as $i => $f) {
                if ($request->request->has('typeteraelis_forumbundle_typesujettype'.$i)) {
                    $f->bind($request);
                    if ($f->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($type_objet[$i]);
                        $em->flush();

                        // On informe le user que c'est bon
                        $this->get('session')->getFlashBag()->add('info', 'Les types de sujet ont été modifiés avec succès');

                        // Puis on redirige vers la page de visualisation de cet article
                        return $this->redirect( $this->generateUrl('admin_typeSujet') );
                    }
                }
            }
        }
        $views = [];
        foreach($form as $i => $f) {
            $views[$i] = $f->createView();
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        return $this->render('TerAelisForumBundle:Admin:typeSujet.html.twig', array(
            'form' => $views,
        ));
    }

    public function typeSujetMonterAction($id, $action) {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:TypeSujet');

        $type_init = $repository->findById($id);

        if ($type_init == null) {
            throw $this->createNotFoundException('TypeSujet inexistant (id = '.$id.')');
        }

        $nMax = $repository->getOrdreMax();

        if (($action === 'up' && $type_init->getOrdre() < $nMax) || ($action === 'down' && $type_init->getOrdre() > 0)) {
            if ($action === 'up') {
                $ordre_init = $type_init->getOrdre() + 1;
            } else {
                $ordre_init = $type_init->getOrdre() - 1;
            }
            $type_init->setOrdre($ordre_init);
            $type_post = $repository->findByOrdre($type_init->getOrdre());

            if ($type_post == null) {
                throw $this->createNotFoundException('TypeSujet inexistant (ordre = '.$type_init->getOrdre().')');
            }

            if ($action === 'up') {
                $ordre_post = $type_init->getOrdre() - 1;
            } else {
                $ordre_post = $type_init->getOrdre() + 1;
            }
            $type_post->setOrdre($ordre_post);

            $em = $this->getDoctrine()->getManager();
            $em->persist($type_init);
            $em->persist($type_post);
            $em->flush();
        }

        // Puis on redirige vers la page de visualisation de cet article
        return $this->redirect( $this->generateUrl('admin_typeSujet') );
    }
}
