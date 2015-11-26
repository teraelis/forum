<?php

namespace TerAelis\ForumBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\CategorieRepository;
use TerAelis\ForumBundle\Entity\ChoixRepository;
use TerAelis\ForumBundle\Entity\FormulaireDonnees;
use TerAelis\ForumBundle\Entity\NonVu;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\ForumBundle\Entity\Sondage;
use TerAelis\CommentBundle\Entity\Thread;
use TerAelis\ForumBundle\Entity\Vote;
use TerAelis\ForumBundle\Form\PostType;
use TerAelis\ForumBundle\Entity\Tag;
use TerAelis\ForumBundle\Form\SondageType;
use TerAelis\ForumBundle\Form\TagType;
use TerAelis\ForumBundle\TerAelisForumBundle;

class PostController extends Controller
{
    const VISIBLE = 0;
    const CORBEILLE = 1;

    /**
     * @param Request $request
     * @param $pole
     * @param $slug
     * @param $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function voirSujetAction(Request $request, $pole, $slug, $page) {
        // On vérifie que la page est valide
        if ($page < 0)
        {
            throw $this->createNotFoundException('Page inexistante (page = '.$page.')');
        }

        // On récupère le sujet
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $sujet = $repository->getSujet($slug);

        if (empty($sujet))
        {
            throw $this->createNotFoundException('Sujet inexistant (slug = '.$slug.')');
        }
        $idSujet = $sujet->getId();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        // On récupère la catégorie principale du sujet
        $categorie = $sujet->getMainCategorie();

        list($pole, $pole_aff) = $this->getPoleAffichage($pole, $categorie->getRoot());
        if($pole_aff !== 'interpole' && $pole !== $pole_aff) {
            return $this->redirect($this->generateUrl(
                'taforum_voirSujet',
                array(
                     'pole' => $pole_aff,
                     'slug' => $slug,
                     'page' => $page,
                )
            ), 301);
        }

        // Exception si pas accessible
        if ($perm['voirSujet'][$categorie->getId()] == 0 || ($sujet->getPublie() == 0 && $perm['moderer'][$categorie->getId()] == 0)) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour voir les sujets dans cette catégorie.");
        }

        // Récupération des threads de discussion
        $threads = $sujet->getThreads($idSujet);

        // Préparation du sondage
        $sondage = $sujet->getSondage();
        $sondageExiste = ($sondage != null);
        $sondageOuvert = false;

        if ($sondageExiste && $perm['voter'][$categorie->getId()] == 1 && !($sondage->getDateFin() != null && $sondage->getDateFin() < new \DateTime())) {
            // L'utilisateur peut potentiellement voter
            // On regarde si l'utilisateur a déjà voté
            $vote = $em->getRepository('TerAelisForumBundle:Vote')
                ->getVote($user, $sondage);
            $sondageOuvert = empty($vote);
            if ($sondageOuvert) {
                // On peut voter
                $choices = $sondage->getChoix();

                $form = $this->createFormBuilder()
                    ->add('choix', 'entity', array(
                        'class'         => 'TerAelisForumBundle:Choix',
                        'data_class'         => 'TerAelis\ForumBundle\Entity\Choix',
                        'choices' => $choices,
                        'property' => 'name',
                        'expanded' => true,
                        'multiple' => false,
                        'required' => true
                    ))
                    ->getForm();

                if ($request->isMethod('POST')) {
                    $form->submit($request);

                    if ($form->isValid()) {
                        $data = $form->getData();
                        $vote = new Vote($user, $sondage);
                        $vote->setChoix($data['choix']);

                        $em->persist($vote);
                        $em->flush();

                        return $this->redirect( $this->generateUrl('taforum_voirSujet', array(
                            'pole' => $pole,
                            'slug' => $slug,
                            'page' => $page
                        )));
                    }
                }
            }
        }

        if ($sondageExiste && !$sondageOuvert) {
            // On peut afficher le sondage
            $choix = $em->getRepository('TerAelisForumBundle:Choix')
                ->findBySondage($sondage);
            $votes = $em->getRepository('TerAelisForumBundle:Vote')
                ->findBySondage($sondage);
            $resultats = [];
            $total = 0;
            foreach($choix as $c) {
                $resultats[$c->getName()] = 0;
            }
            foreach($votes as $vote) {
                $c = $vote->getChoix();
                $name = $c->getName();
                $resultats[$c->getName()]++;
                $total++;
            }
            foreach($resultats as $k => $r) {
                $resultats[$k] = array(
                    'percentage' => $r/$total*100,
                    'number' => $r,
                );
            }
        }

        // Path de la catégorie
        $path = $em->getRepository('TerAelisForumBundle:Categorie')->getPath($categorie);

        if(!empty($user)) {
            $nonVu = $em->getRepository('TerAelisForumBundle:NonVu')->findUserPost($user, $sujet);
            if (!empty($nonVu)) {
                $nonVu = current($nonVu);
            } else {
                $nonVu = null;
            }
        } else {
            $nonVu = null;
        }

        // On l'affiche
        $renderArray = array(
            'user' => $user,
            'categorie' => $categorie,
            'path' => $path,
            'sujet' => $sujet,
            'tags' => $sujet->getTags(),
            'pole' => $pole,
            'slug' => $slug,
            'threads' => $threads,
            'sondageExiste' => $sondageExiste,
            'editerMessage' => $perm['editerMessage'][$categorie->getId()] && $sujet->isAuthor($user),
            'supprimerMessage' => $perm['supprimerMessage'][$categorie->getId()] && $sujet->isAuthor($user) && $sujet->getNumberComment() == 0,
            'repondreSujet' => $perm['repondreSujet'][$categorie->getId()],
            'moderer' => $perm['moderer'][$categorie->getId()],
            'perm' => $perm,
            'page' => $page,
            'nonVu' => $nonVu
        );

        if($renderArray['moderer']) {
            $renderArray['editerMessage'] = 1;
            $renderArray['supprimerMessage'] = 1;
        }

        $threads = $sujet->getThreads();
        $lock = false;
        foreach($threads as $t) {
            $lock = $t->getLock() || $lock;
        }
        $renderArray['lock'] = $lock;

        if ($sondage != null) {
            $renderArray['question'] = $sondage->getQuestion();
            $renderArray['choix'] = $sondage->getChoix();
            $renderArray['sondageOuvert'] = $sondageOuvert;
            if (isset($resultats) && $resultats != null) {
                $renderArray['choix'] = $resultats;
            }
        }

        if ($sondageOuvert == 1) {
            $renderArray['form'] = $form->createView();
        }

        // On ajoute la vue
        $this->container->get('ter_aelis_views.views')
            ->postView($this->container->get('request')->getClientIp(), $categorie, $sujet);
        if($pole == "blog") {
            $dossier = "Blog";
        } else {
            $dossier = "Forum";
        }

        // On a lu
        if($user != null) {
            $nonVuService = $this->container->get('ter_aelis_forum.non_vu');
            $nonVuService->deleteNonVu($user, $sujet);
        }

        $this->get('session')->set('pole_aff', $pole);
        $response = $this->render('TerAelisForumBundle:'.$dossier.':voirSujet.html.twig', $renderArray);
        $response->headers->setCookie(new Cookie('pole_aff', $pole));

        return $response;
    }

    public function creerSujetAction(Request $request, $pole, $slug, $upgrade)
    {
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie');
        $categorie = $repository->findOneBy(array('slug' => $slug));

        if (empty($categorie)) {
            throw $this->createNotFoundException('Categorie inexistante (catégorie = '.$slug.')');
        }

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        if ($perm['creerSujet'][$categorie->getId()] == 0) {
            throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour poster dans cette catégorie.");
        }

        // Création du post
        $post = new Post();
        // On ajoute l'article dans la catégorie
        if ($user != null) {
            $post->addAuthor($user);
        }
        $date = new \DateTime();
        $post->setDatePublication($date);
        $post->setMainCategorie($categorie);
        $post->addCategory($categorie);
        $post->setPublie(true);

        // On ajoute les formulaires a remplir
        $types = [];
        $i = 0;
        $session = $request->getSession();
        foreach($categorie->getFormulaire() as $formulaire) {
            $donnee = new FormulaireDonnees();
            $donnee->setPost($post);
            $donnee->setType($formulaire);
            if($upgrade == 1 && $session->has('body')) {
                $donnee->setContenu($session->get('body'));
                $session->remove('body');
            } else {
                $donnee->setContenu($formulaire->getDefault());
            }
            $types[$i] = $formulaire;
            $i++;
            $post->addFormulaireDonnee($donnee);
        }

        // On crée le formulaire
        $form = $this->createForm(new PostType(), $post);

        $categoriePost = $categorie;
        $categories = array($categorie);

        // Path de la catégorie
        $path = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie')
            ->getPath($categorie);

        $returnArray = array();
        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->submit($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                if($form->get('previsualiser')->isClicked()) {
                    $returnArray['previsualiser'] = $form->getData();
                } else {
                    $em = $this->get('doctrine.orm.entity_manager');

                    $date = new \DateTime();
                    if ($post->getDatePublication() < $date) {
                        $post->setDatePublication($date);
                    }
                    $post->setEstPremierPost(true);
                    $post->setEstVerrouille(false);
                    $post->setLastComment($post->getDatePublication());
                    $post->setCreatedAt(new \DateTime());

                    $lastPost = $em->getRepository('TerAelisForumBundle:Post')
                        ->findLastByUser($user->getId());

                    $lastPostMaxTime = new \DateTime();
                    $lastPostMaxTime->modify('-10 seconds');
                    if(!empty($lastPost) && $lastPost->getCreatedAt()->getTimestamp() > $lastPostMaxTime->getTimestamp()) {
                        $returnArray['notAllowed'] = 'Votre dernier sujet a été créé il y a trop peu de temps. Vérifiez qu\'il n\'a pas déjà été créé avant de continuer';
                    } else {
                        $em->beginTransaction();
                        // Sondage
                        $sondage = $post->getSondage();
                        if(!empty($sondage)) {
                            $sondage->setChoixMultiples(false);
                            $question = $sondage->getQuestion();
                            if ($question == "" || $sondage->getChoix()->isEmpty()) {
                                $post->setSondage(null);
                            } else {
                                $em->persist($sondage);
                                $em->flush();
                                foreach ($sondage->getChoix() as $choix) {
                                    $choix->setSondage($sondage);
                                    $em->persist($choix);
                                    $em->flush();
                                }
                            }
                        }

                        // Tags
                        $tagArray = array();
                        $tagSlugs = array();
                        $tags = $post->getTags();
                        // On lie les tags à ceux qui existent déjà
                        if (!empty($tags)) {
                            foreach ($tags as $t) {
                                $tagArray[$t->getSlug()] = $t;
                                $tagSlugs[] = $t->getSlug();
                            }
                        }

                        if(!empty($tagArray)) {
                            $tags = $em->getRepository('TerAelisForumBundle:Tag')
                                ->findBySlugs($tagSlugs);

                            $post->setTagsNull();
                            foreach ($tags as $index => $t) {
                                $post->addTag($t);
                                unset($tagArray[$t->getSlug()]);
                            }

                            /* On crée les nouveaux tags */
                            if (count($tagArray) > 0) {
                                foreach ($tagArray as $tag) {
                                    $post->addTag($tag);
                                }
                            }
                        }

                        // Commentaires
                        $thread = new Thread();
                        $thread->setPost($post);
                        $thread->setLastComment($date);
                        $em->persist($thread);
                        $em->flush();


                        $post->setLastComment($date, true);
                        $post->setLastAuthor($user);

                        // On l'ajoute dans la BDD
                        $em->persist($post);
                        $em->flush();

                        // On prévient les users
                        $nonVuService = $this->container->get('ter_aelis_forum.non_vu');
                        $nonVuService->updateNonVu($post);

                        // Update des infos des categories
                        $categoriesId = array();

                        while ($categorie != null) {
                            $categoriesId[$categorie->getId()] = $categorie->getId();
                            $categorie = $categorie->getParent();
                        }
                        $this->get('ter_aelis_forum.post_statistics')->refreshCategories($categoriesId);

                        // On informe le user que c'est bon
                        $this->get('session')->getFlashBag()->add('info', 'Le sujet a été créé avec succès');

                        $page = intval(1 + $thread->getNumberComment() / $this->container->getParameter('nb_commentaires'));
                        $em->commit();

                        // Puis on redirige vers la page de visualisation de cet article
                        return $this->redirect($this->generateUrl('taforum_voirSujet', array(
                            'pole' => $pole,
                            'slug' => $post->getSlug(),
                            'page' => $page
                        )));
                    }
                }
            }
        }

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        $returnArray = array_merge($returnArray, array(
                                                      'pole' => $pole,
                                                      'pole_aff' => $pole,
                                                      'categories' => $categories,
                                                      'viewBalise' => $post->getMainCategorie()->hasBalise(),
                                                      'creerSpecial' => (int) $perm['creerSpecial'][$post->getMainCategorie()->getId()],
                                                      'moderer' => $perm['moderer'][$categoriePost->getId()],
                                                      'creerSondage' => $perm['creerSondage'][$categoriePost->getId()],
                                                      'form' => $form->createView(),
                                                      'type' => $types,
                                                      'path' => $path
                                                 ));
        return $this->render('TerAelisForumBundle:Forum:creerSujet.html.twig', $returnArray);
    }

    public function editerSujetAction($pole, $id)
    {
        $returnArray = [];
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $sujet = $repository->findOneBy(array('id' => $id));

        if (empty($sujet)) {
            throw $this->createNotFoundException('Sujet/réponse inexistant (id = '.$id.')');
        }

        $categorie = $sujet->getMainCategorie();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        if (!$permService->hasRight('moderer', $categorie->getId(), $perm)) {
            if (!$permService->hasRight('editerMessage', $categorie->getId(), $perm) || !$sujet->isAuthor($user)) {
                throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour éditer les sujets dans cette catégorie.");
            }
        }

        // On ajoute les formulaires a remplir
        // On récupère les formulaires originaux
        $formulairesOriginaux = [];
        foreach($sujet->getFormulaireDonnees() as $formulaire) {
            $type = $formulaire->getType();
            $formulairesOriginaux[] = $type->getId();
        }

        // On mets les formulaires nouveaux
        $types = [];
        $i = 0;
        foreach($categorie->getFormulaire() as $formulaire) {
            if (!in_array($formulaire->getId(), $formulairesOriginaux)) {
                $donnee = new FormulaireDonnees();
                $donnee->setPost($sujet);
                $donnee->setType($formulaire);
                $donnee->setContenu($formulaire->getDefault());
                $sujet->addFormulaireDonnee($donnee);
            }
            $types[$i] = $formulaire;
            $i++;
        }

        // Création du formulaire
        $datePublication = $sujet->getDatePublication();
        $now = new \DateTime();
        $wasPublished = $datePublication <= $now;
        $hasDatePublication = !$wasPublished;
        $form = $this->createForm(new PostType, $sujet, array(
            'has_date_publication' => $hasDatePublication,
        ));

        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->submit($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                if($form->get('previsualiser')->isClicked()) {
                    $returnArray['previsualiser'] = $form->getData();
                } else {
                    if(!$wasPublished && $sujet->getDatePublication() < $now) {
                        $sujet->setDatePublication($now);
                    }

                    $sujet->setDateEdition($now);

                    $em->beginTransaction();

                    // On s'occupe du sondage
                    $sondage = $sujet->getSondage();
                    $question = $sondage->getQuestion();
                    if ($question == "" || $sondage->getChoix()->isEmpty()) {
                        $sujet->setSondage(null);
                    } else {
                        $em->persist($sondage);
                        $em->flush();
                        foreach($sondage->getChoix() as $choix) {
                            $choix->setSondage($sondage);
                            $em->persist($choix);
                            $em->flush();
                        }
                    }

                    // Tags
                    $tagArray = array();
                    $tagSlugs = array();
                    $tags = $sujet->getTags();
                    // On lie les tags à ceux qui existent déjà
                    if (!empty($tags)) {
                        foreach ($tags as $t) {
                            $tagArray[$t->getSlug()] = $t;
                            $tagSlugs[] = $t->getSlug();
                        }
                    }

                    if(!empty($tagArray)) {
                        $tags = $em->getRepository('TerAelisForumBundle:Tag')
                            ->findBySlugs($tagSlugs);

                        $sujet->setTagsNull();
                        foreach ($tags as $index => $t) {
                            $sujet->addTag($t);
                            unset($tagArray[$t->getSlug()]);
                        }

                        /* On crée les nouveaux tags */
                        if (count($tagArray) > 0) {
                            foreach ($tagArray as $tag) {
                                $sujet->addTag($tag);
                            }
                        }
                    }


                    // On l'ajoute dans la BDD
                    $em->persist($sujet);
                    $em->flush();

                    // Update stats des catégories
                    $categorie = $sujet->getMainCategorie();
                    $categoriesId = array();
                    while ($categorie != null) {
                        $categoriesId[$categorie->getId()] = $categorie->getId();
                        $categorie = $categorie->getParent();
                    }
                    $this->get('ter_aelis_forum.post_statistics')->refreshCategories($categoriesId);

                    // On informe le user que c'est bon
                    $this->get('session')->getFlashBag()->add('info', 'Le sujet a été modifié avec succès');

                    $page = intval(1 + ($sujet->getThreads()->first()->getNumberComment() - 1) / $this->container->getParameter('nb_commentaires'));

                    $em->commit();
                    // Puis on redirige vers la page de visualisation de cet article
                    return $this->redirect( $this->generateUrl('taforum_voirSujet', array(
                        'pole' => $pole,
                        'slug' => $sujet->getSlug(),
                        'page' => $page
                    )) );
                }
            }
        }

        // Path de la catégorie
        $path = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisForumBundle:Categorie')
            ->getPath($categorie);

        // Il n'y a pas encore eu de requête, on affiche le formulaire
        $returnArray = array_merge($returnArray, array(
            'pole' => $pole,
            'form' => $form->createView(),
            'viewBalise' => $categorie->hasBalise(),
            'creerSpecial' => (int) $perm['creerSpecial'][$categorie->getId()],
            'creerSondage' => (int) $perm['creerSondage'][$categorie->getId()],
            'moderer' => (int) $perm['moderer'][$categorie->getId()],
            'type' => $types,
            'path' => $path
        ));

        return $this->render('TerAelisForumBundle:Forum:creerSujet.html.twig', $returnArray);
    }

    public function supprimerAction(Request $request, $pole, $id) {
        // On récupère le sujet
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $sujet = $repository->findOneById($id);

        if (empty($sujet))
        {
            throw $this->createNotFoundException('Sujet inexistant (id = '.$id.')');
        }

        $form = $this->createFormBuilder()
                ->getForm();

        // On récupère la catégorie principale du sujet
        $categorie = $sujet->getMainCategorie();

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        if (!$permService->hasRight('moderer', $categorie->getId(), $perm)) {
            if (!$permService->hasRight('supprimerMessage', $categorie->getId(), $perm) || !$sujet->isAuthor($user)) {
                throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour supprimer les sujets dans cette catégorie.");
            } else {
                if($sujet->getNumberComment() > 0) {
                    throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour supprimer un sujet ayant déjà des commentaires dans cette catégorie.");
                }
            }
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->beginTransaction();

            $this->get('ter_aelis_forum.non_vu')->deleteNonVu(
                null,
                $sujet
            );

            $em->remove($sujet);
            $em->flush();

            // Mise a jour des infos des categories
            $parent = $categorie;
            $categoriesId = [];
            while ($parent != null) {
                $categoriesId[] = $parent->getId();
                $parent = $parent->getParent();
            }
            $this->get('ter_aelis_forum.post_statistics')->refreshCategories($categoriesId);

            $em->commit();

            // On définit un message flash
            $this->get('session')->getFlashBag()->add('info', 'Sujet bien supprimé');

            // Puis on redirige vers l'accueil
            return $this->redirect($this->generateUrl('taforum_forum', array('pole' => $pole, 'slug' => $categorie->getSlug())));
        }

        return $this->render('TerAelisForumBundle:Forum:supprimerSujet.html.twig', array(
            'pole'      => $pole,
            'sujet'     => $sujet,
            'form'      => $form->createView()
        ));
    }

    public function voirSujetLastAction($pole, $slug) {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        if(!empty($user)) {
            $repoNonVu = $em->getRepository('TerAelisForumBundle:NonVu');
            $nonVu = $repoNonVu->findByPostSlug($user, $slug);
        } else {
            $nonVu = null;
        }
        $nbCommentByPage = $this->container->getParameter('nb_commentaires');
        $arrayForward = array(
            'pole' => $pole,
            'slug' => $slug,
        );

        $idComment = null;
        if($nonVu == null) {
            // On va a la dernière page
            $post = $em->getRepository('TerAelisForumBundle:Post')->findOneBySlug($slug);
            $numberComment = $post->getNumberComment();
            $page = ($numberComment - 1) / $nbCommentByPage;
            $arrayForward['page'] = (int) $page + 1;
        } else {
            $post = $nonVu->getPost();
            $comment = $nonVu->getComment();
            if($comment != null) {
                $numberCommentOlder = $em->getRepository('TerAelisCommentBundle:Comment')
                    ->getNumberCommentOlder($post, $comment);
                $page = ($post->getNumberComment() - $numberCommentOlder - 1) / $nbCommentByPage;
                $arrayForward['page'] = (int) $page + 1;
                $idComment = $comment->getId();
            }

        }
        $url = $this->generateUrl('taforum_voirSujet', $arrayForward);
        if($idComment != null) {
            $url .= '#comment'.$idComment;
        } else if (!empty($user)) {
            $url .= '#last';
        }
        return $this->redirect($url);
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

    public function lockAction(Request $request, $pole, $id)
    {
        // On récupère le sujet
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $sujet = $repository->findOneById($id);

        if (empty($sujet))
        {
            throw $this->createNotFoundException('Sujet inexistant (id = '.$id.')');
        }

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        // On récupère la catégorie principale du sujet
        $categorie = $sujet->getMainCategorie();

        if(!isset($perm['moderer'][$categorie->getId()]) || empty($perm['moderer'][$categorie->getId()]) || $perm['moderer'][$categorie->getId()] != 1) {
            throw new AccessDeniedException('Vous n\'avez pas le droit de vérouiller ce sujet.');
        }

        $threads = $sujet->getThreads();
        $lock = true;
        foreach($threads as $t) {
            $lock = $lock && $t->getLock();
        }

        $form = $this->createFormBuilder()
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            foreach($threads as $t) {
                $t->setLock(!$lock);
                $em->persist($t);
            }
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'taforum_voirSujet',
                    array(
                        'pole' => $pole,
                        'slug' => $sujet->getSlug(),
                    )
                )
            );
        }

        if($lock) {
            $action = 'Dévérouiller';
        } else {
            $action = 'Vérouiller';
        }

        return $this->render(
            '@TerAelisForum/Moderer/deplacer.html.twig',
            array(
                'pole' => $pole,
                'form' => $form->createView(),
                'title' => $action.' un sujet',
                'button' => $action
            )
        );
    }

    public function publieAction(Request $request, $pole, $id) {
        // On récupère le sujet
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em
            ->getRepository('TerAelisForumBundle:Post');
        $sujet = $repository->findOneById($id);

        if (empty($sujet))
        {
            throw $this->createNotFoundException('Sujet inexistant (id = '.$id.')');
        }

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        // On récupère la catégorie principale du sujet
        $categorie = $sujet->getMainCategorie();

        if(!isset($perm['moderer'][$categorie->getId()]) || empty($perm['moderer'][$categorie->getId()]) || $perm['moderer'][$categorie->getId()] != 1) {
            throw new AccessDeniedException('Vous n\'avez pas le droit de vérouiller ce sujet.');
        }

        $publie = $sujet->getPublie();

        $form = $this->createFormBuilder()
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->beginTransaction();

            $sujet->setPublie(!$sujet->getPublie());
            $em->flush();

            if(!$sujet->getPublie()) {
                $this->get('ter_aelis_forum.non_vu')
                    ->deleteNonVu(null, $sujet);
            } else {
                $this->get('ter_aelis_forum.non_vu')
                    ->updateNonVu($sujet);
            }

            $categoriesIds = array();
            while(!empty($categorie)) {
                $categoriesIds[$categorie->getId()] = $categorie->getId();
                $categorie = $categorie->getParent();
            }
            $this->get('ter_aelis_forum.post_statistics')
                ->refreshCategories(
                    $categoriesIds
                );

            $em->commit();

            return $this->redirect(
                $this->generateUrl(
                    'taforum_voirSujet',
                    array(
                        'pole' => $pole,
                        'slug' => $sujet->getSlug(),
                    )
                )
            );
        }

        if($publie) {
            $action = 'Retirer';
        } else {
            $action = 'Publier';
        }

        return $this->render(
            '@TerAelisForum/Moderer/deplacer.html.twig',
            array(
                'pole' => $pole,
                'form' => $form->createView(),
                'title' => $action.' un sujet',
                'button' => $action
            )
        );
    }
}
