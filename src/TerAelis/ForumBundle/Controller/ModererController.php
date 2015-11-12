<?php

namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\CommentBundle\Entity\CommentRepository;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\CategorieRepository;
use TerAelis\ForumBundle\Entity\FormulaireDonnees;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\ForumBundle\Entity\PostRepository;


class ModererController extends Controller
{
    const VISIBLE = 0;
    const CORBEILLE = 1;
    const BLOG = 2;
    const CORBEILLE_ROOT = 61;

    public function deplacerListeSujetsAction(Request $request, $pole, $id, $page = 1, $type = self::VISIBLE) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('TerAelisForumBundle:Categorie');

        // On récupère la catégorie
        $categorie = $repository->findOneById($id);

        if($categorie == null) {
            throw $this->createNotFoundException("Cette catégorie est introuvable (id = ".$id.")");
        }

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        $categorieId = $categorie->getId();
        if (!$permService->hasRight('moderer', $categorieId, $perm)) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour déplacer les sujets dans cette catégorie.");
        }

        $path = $repository->getPath($categorie);
        $form = $this->createFormBuilder()
            ->add('categorie', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Categorie',
                    'query_builder' => function(CategorieRepository $cr) use ($type) {
                        return $cr->findByType($type);
                    },
                    'property' => 'title',
                    'multiple' => false,
                    'required' => true
            ))
            ->add('posts', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Post',
                    'query_builder' => function(PostRepository $pr) use ($categorie) {
                        return $pr->createQueryBuilder('p')
                            ->join('p.mainCategorie', 'c')
                            ->addSelect('c')
                            ->where('c.id = '.$categorie->getId());
                    },
                    'property' => 'title',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true
            ))
            ->getForm();


        $posts = $em->getRepository('TerAelisForumBundle:Post')
            ->findByMainCategorie($categorie);
        $arrayPosts = null;
        foreach($posts as $p) {
            $arrayPosts[$p->getId()] = $p;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->beginTransaction();
                // On récupère la nouvelle catégorie
                $newCategorie = $form['categorie']->getData();

                // On récupère les posts
                $selectionnes = $form['posts']->getData();
                $lastComment = new \DateTime('2001-01-01');
                $numberComment = 0;
                $numberPost = 0;
                $lastPost = null;
                // On déplace chaque post un a un
                foreach ($selectionnes as $p) {
                    // On recalcule le dernier commentaire
                    $tmpLastComment = $p->getLastComment();
                    if($tmpLastComment > $lastComment) {
                        $lastComment = $tmpLastComment;
                        $lastPost = $p;
                    }
                    $numberComment = $numberComment + $p->getNumberComment();
                    $numberPost = $numberPost + 1;

                    // On définie la nouvelle catégorie
                    $categorie = $p->getMainCategorie();
                    $p->setMainCategorie($newCategorie);

                    // On l'enlève de la catégorie
                    $p->removeCategory($categorie);
                    $p->addCategory($newCategorie);

                    // Récupération du contenu
                    $arrayFormulaireDonnees = [];
                    $body = "";
                    foreach($p->getFormulaireDonnees() as $fd) {
                        $arrayFormulaireDonnees[] = $fd;
                        $body = $body.$fd->getContenu();
                    }

                    // Création du nouveau contenu
                    foreach($p->getMainCategorie()->getFormulaire() as $ft) {
                        $fd = new FormulaireDonnees();
                        $fd->setPost($p);
                        $fd->setType($ft);
                        $fd->setContenu($body);
                        $body = "";
                        $p->addFormulaireDonnee($fd);
                    }

                    // Suppression des anciens formulaires
                    foreach($arrayFormulaireDonnees as $fd) {
                        $p->removeFormulaireDonnee($fd);
                        $em->remove($fd);
                    }
                    $em->flush();

                    // On retire la balise
                    $p->setBalise();

                    // On met a jour le post
                    $em->persist($p);
                    $em->flush();
                }

                // On mets a jour les infos des catégories
                $categorieId = $categorie->getId();
                $categoriesId = [];
                while(!empty($categorie)) {
                    $categoriesId[$categorie->getId()] = $categorie->getId();
                    $categorie = $categorie->getParent();
                }
                $categorie = $newCategorie;
                while(!empty($categorie)) {
                    $categoriesId[$categorie->getId()] = $categorie->getId();
                    $categorie = $categorie->getParent();
                }
                $postStatsService = $this->container->get('ter_aelis_forum.post_statistics');
                $postStatsService->refreshCategories($categoriesId);

                $em->commit();
                return $this->redirect($this->generateUrl('taforum_deplacer_liste', array(
                    'pole' => $pole,
                    'id' => $categorieId
                )));
            }
        }

        return $this->render('TerAelisForumBundle:Moderer:deplacerliste.html.twig', array(
            'pole' => $pole,
            'path' => $path,
            'form' => $form->createView(),
            'posts' => $arrayPosts,
            'title' => ($type === self::VISIBLE ? 'Déplacer' : 'Archiver').' une liste de sujet',
            'button' => $type === self::VISIBLE ? 'Déplacer' : 'Archiver',
        ));
    }

    public function deplacerSujetAction($pole, $id, $type = self::VISIBLE) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $post = $repository->findOneById($id);

        if (empty($post))
        {
            throw $this->createNotFoundException('Sujet inexistant (id = '.$id.')');
        }

        // On récupère la catégorie principale du sujet
        $categorie = $post->getMainCategorie();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        $categorieId = $categorie->getId();
        if (!$permService->hasRight('moderer', $categorieId, $perm)) {
            if ($type === self::VISIBLE) {
                throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour déplacer les sujets dans cette catégorie.");
            } else {
                if($permService->hasRight('supprimerMessage', $categorieId, $perm)) {
                    return $this->redirect(
                        $this->generateUrl(
                            'taforum_delete_sujet',
                            array(
                                'pole' => $pole,
                                'id' => $id
                            )
                        )
                    );
                } else {
                    throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour déplacer les sujets dans cette catégorie.");
                }
            }
        }

        $form = $this->createFormBuilder($post)
            ->add('mainCategorie', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Categorie',
                    'query_builder' => function(CategorieRepository $cr) use ($type) {
                        return $cr->findByType($type);
                    },
                    'property' => 'title',
                    'multiple' => false,
                    'required' => true
                ))
            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                $em->beginTransaction();
                // On l'enlève de la catégorie
                $post->removeCategory($categorie);
                $mainCategorie = $post->getMainCategorie();
                $post->addCategory($mainCategorie);

                $categoriesId = array();
                while(!empty($categorie)) {
                    $categoriesId[$categorie->getId()] = $categorie->getId();
                    $categorie = $categorie->getParent();
                }
                $categorie = $mainCategorie;
                while(!empty($categorie)) {
                    $categoriesId[$categorie->getId()] = $categorie->getId();
                    $categorie = $categorie->getParent();
                }

                // Récupération du contenu
                $arrayFormulaireDonnees = [];
                $body = "";
                foreach($post->getFormulaireDonnees() as $fd) {
                    $arrayFormulaireDonnees[] = $fd;
                    $body .= $fd->getContenu();
                }

                // Création du nouveau contenu
                foreach($mainCategorie->getFormulaire() as $ft) {
                    $fd = new FormulaireDonnees();
                    $fd->setPost($post);
                    $fd->setType($ft);
                    $fd->setContenu($body);
                    $body = "";
                    $post->addFormulaireDonnee($fd);
                }

                // Suppression des anciens formulaires
                foreach($arrayFormulaireDonnees as $fd) {
                    $post->removeFormulaireDonnee($fd);
                    $em->remove($fd);
                }
                $em->flush();

                // On met a jour le post
                $em->persist($post);
                $em->flush();

                // On s'assure de la bonne vision des derniers messages et des stats
                $postStatsService = $this->container->get('ter_aelis_forum.post_statistics');
                $postStatsService->refreshCategories($categoriesId);

                $em->commit();
                return $this->redirect($this->generateUrl('taforum_voirSujet', array(
                    'pole' => $pole,
                    'slug' => $post->getSlug()
                )));
            }
        }

        $renderArray = array(
            'pole' => $pole,
            'form' => $form->createView(),
        );

        if ($type === self::CORBEILLE) {
            $renderArray['title'] = 'Archiver un sujet';
            $link = $this->generateUrl(
                'taforum_delete_sujet',
                array(
                    'pole' => $pole,
                    'id' => $id,
                )
            );
            $renderArray['description'] = 'En archivant un sujet, il n\'est plus accessible au public, mais est conservé en base de données.<br/><a href="'.$link.'">Pour supprimer définitivement cliquez ici.</a>';
            $renderArray['button'] = "Archiver";
        } else {
            $renderArray['title'] = 'Déplacer un sujet';
            $renderArray['button'] = "Déplacer";
        }
        return $this->render('TerAelisForumBundle:Moderer:deplacer.html.twig', $renderArray);
    }

    public function upgrade1Action($pole, $id) {
        $form = $this->createFormBuilder()
            ->add('type', 'choice', array(
                'choices'   => array(
                    self::VISIBLE   => 'Forum',
                    self::BLOG => 'Blog',
                ),
                'multiple'  => false,
            ))
            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $choix = $form['type']->getData();
                return $this->redirect($this->generateUrl('taforum_creer_post_depuis_comment2', array(
                    'pole'  => $pole,
                    'id' => $id,
                    'type' => (int) $choix
                )));
            }
        }

        return $this->render('TerAelisForumBundle:Moderer:upgrade.html.twig', array(
            'pole' => $pole,
            'form' => $form->createView()
        ));
    }

    public function upgrade2Action($pole, $id, $type) {
        // On récupèré le commentaire
        $em = $this->getDoctrine()
            ->getManager();
        $repository = $em
            ->getRepository('TerAelisCommentBundle:Comment');
        $comment = $repository->findOneById($id);
        if (empty($comment))
        {
            throw $this->createNotFoundException('Commentaire inexistant (id = '.$id.')');
        }
        // On récupère la catégorie principale du sujet
        $post = $comment->getThread()->getPost();
        $categorie = $post->getMainCategorie();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        if ($perm['moderer'][$categorie->getId()] == 0 && $post->isAuthor($user) == 0) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour créer un post a partir de ce commentaire.");
        }

        $form = $this->createFormBuilder()
            ->add('categorie', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Categorie',
                    'query_builder' => function(CategorieRepository $cr) use ($type) {
                        return $cr->findByType($type);
                    },
                    'property' => 'title',
                    'multiple' => false,
                    'required' => true)
            )
            ->getForm();

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $categorie = $form['categorie']->getData();
                $session = $this->getRequest()->getSession();
                $session->set('body', $comment->getBody());
                return $this->redirect($this->generateUrl('taforum_creerSujet', array(
                    'pole'  => $pole,
                    'slug' => $categorie->getSlug(),
                    'upgrade' => 1
                )));
            }
        }

        return $this->render('TerAelisForumBundle:Moderer:upgrade.html.twig', array(
            'pole' => $pole,
            'form' => $form->createView()
        ));
    }

    public function publierListeAction(Request $request, $pole, $id, $page = 1) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('TerAelisForumBundle:Categorie');

        // On récupère la catégorie
        $categorie = $repository->findOneById($id);

        if($categorie == null) {
            throw $this->createNotFoundException("Cette catégorie est introuvable (id = ".$id.")");
        }

        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        $categorieId = $categorie->getId();
        if (!$permService->hasRight('moderer', $categorieId, $perm)) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour modérer cette catégorie.");
        }

        $path = $repository->getPath($categorie);

        $form = $this->createFormBuilder()
            ->add('posts', 'entity', array(
                'class'    => 'TerAelisForumBundle:Post',
                'query_builder' => function(PostRepository $pr) use ($categorie) {
                    return $pr->createQueryBuilder('p')
                        ->where('p.publie = 0')
                        ->join('p.mainCategorie', 'c')
                        ->addSelect('c')
                        ->andwhere('c.id = '.$categorie->getId());
                },
                'property' => 'title',
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ))
            ->getForm();

        $posts = $em->getRepository('TerAelisForumBundle:Post')
            ->findBy(array('mainCategorie' => $categorie, 'publie' => 0));
        $arrayPosts = null;
        foreach($posts as $p) {
            $arrayPosts[$p->getId()] = $p;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // On récupère les posts
                $em->beginTransaction();

                $selectionnes = $form['posts']->getData();
                foreach($selectionnes as $p) {
                    $p->setPublie(true);
                    $em->persist($p);
                }
                $em->flush();

                $categorieId = $categorie->getId();
                $categoriesId = array();
                while(!empty($categorie)) {
                    $categoriesId[$categorie->getId()] = $categorie->getId();
                    $categorie = $categorie->getParent();
                }
                $postStatsService = $this->container->get('ter_aelis_forum.post_statistics');
                $postStatsService->refreshCategories($categoriesId);

                $em->commit();

                return $this->redirect($this->generateUrl('taforum_valider_liste', array(
                    'pole' => $pole,
                    'id' => $categorieId,
                )));
            }
        }

        return $this->render('TerAelisForumBundle:Moderer:valider_liste.html.twig', array(
            'pole' => $pole,
            'path' => $path,
            'form' => $form->createView(),
            'posts' => $arrayPosts
        ));
    }

    public function duppliquerListeAction($pole, $id) {
        // On récupère le post
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('TerAelisForumBundle:Post');
        $post = $postRepo
            ->findOneBy(array('id' => $id));
        if($post == null) {
            throw new $this->createNotFoundException("Post introuvable (id = ".$id.")");
        }

        $categorie = $post->getMainCategorie();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        $categorieId = $categorie->getId();
        if (!$permService->hasRight('moderer', $categorieId, $perm)) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour modérer cette catégorie.");
        }

        // On récupère le formulaire
        $form = $this->createFormBuilder($post)
            ->add('categories', 'entity', array(
                'class'    => 'TerAelisForumBundle:Categorie',
                'query_builder' => function(CategorieRepository $cr) use ($perm, $user) {
                    return $cr->createQueryBuilder('c')
                        ->orderBy('c.root')
                        ->addOrderBy('c.lft');
                },
                'property' => 'title',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
            ))
            ->getForm();

        $categories = $em->getRepository('TerAelisForumBundle:Categorie')
            ->createQueryBuilder('c')
            ->orderBy('c.root')
            ->addOrderBy('c.lft')
            ->getQuery()
            ->getResult();
        $arrayCategories = [];
        foreach($categories as $c) {
            $arrayCategories[$c->getId()] = $c;
        }

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($post);
                $em->flush();

                return $this->redirect($this->generateUrl('taforum_duppliquer_liste_categorie', array(
                    'pole'  => $pole,
                    'id'    => $id
                )));
            }
        }
        return $this->render('TerAelisForumBundle:Moderer:duppliquer_liste_categories.html.twig', array(
            'pole' => $pole,
            'form' => $form->createView(),
            'categories' => $arrayCategories,
            'perm' => $perm,
            'sondage' => $post->getSondage() != null,
            'special' => $post->getTypeSujet() != null
        ));
    }
}