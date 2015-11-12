<?php
namespace TerAelis\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\CommentBundle\Entity\Comment;
use TerAelis\CommentBundle\Entity\Thread;
use TerAelis\CommentBundle\Form\CommentType;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\UserBundle\Entity\User;

class ThreadController extends Controller
{
    public function getThreadAction($pole, Categorie $categorie, $slug, Post $post, Thread $thread, $nonVu = null, $moderer = false, User $user = null, $perm = null, $page = 1, $answer = 0) {
        if ($perm == null) {
            $permService = $this->container->get('ter_aelis_forum.permission');
            $perm = $permService->getPerm($user);
        }

        $voirCategorie = $perm['voirCategorie'][$categorie->getId()];
        $voirSujet = $perm['voirSujet'][$categorie->getId()];
        $repondreSujet = $perm['repondreSujet'][$categorie->getId()];

        if ($voirCategorie == 0 || $voirSujet == 0) {
            throw new AccessDeniedException("Interdiction de voir ce sujet (idCategorie = ".$categorie->getId().")");
        }

        list($pole_aff, $pole) = $this->getPoleAffichage($pole, $categorie->getRoot());

        $nbCommentPerPage = $this->container->getParameter('comment.nb_comment');
        $returnArray = array(
            'user'          => $user,
            'answer'        => $answer,
            'slug'          => $slug,
            'thread'        => $thread,
            'repondreSujet' => $repondreSujet,
            'pole'          => $pole,
            'pole_aff'      => $pole_aff,
            'nbCommentPerPage' => $nbCommentPerPage,
            'moderer'       => $moderer,
            'sujet'          => $post,
            'page'          => $page,
            'nonVu'         => $nonVu
        );

        $em = $this->getDoctrine()->getManager();

        $comments = null;
        if ($thread->getNumberComment() > 0) {
            $editerMessage = $perm['editerMessage'][$categorie->getId()];
            $supprimerMessage = $perm['supprimerMessage'][$categorie->getId()];
            $moderer = $perm['moderer'][$categorie->getId()];
            $repositoryComments = $em->getRepository('TerAelisCommentBundle:Comment');
            $comments = $repositoryComments->findByThread($thread, $nbCommentPerPage, $page, $answer);

            // Variables transmises au twig
            $returnArray['comments'] = $comments;
            $returnArray['editerMessage'] = $editerMessage;
            $returnArray['supprimerMessage'] = $supprimerMessage;
            $returnArray['postLastComment'] = $post->getLastComment();
            $returnArray['moderer'] = $moderer;
        }

        // Update des non lus
        if($user != null) {
            $lastCommentShown = null;
            if($comments != null) {
                foreach($comments as $c) {
                    $lastCommentShown = $c;
                }
            }
            $nonVuService = $this->container->get('ter_aelis_forum.non_vu');
            $nonVuService->deleteNonVu($user, $post, $lastCommentShown);
        }

        return $this->render('TerAelisCommentBundle:Thread:view.html.twig', $returnArray);
    }

    public function answerAction(Request $request, $pole, $id, $body = '') {
        $user = $this->getUser();
        $permService = $this->container->get('ter_aelis_forum.permission');
        $perm = $permService->getPerm($user);

        $em = $this->getDoctrine()->getManager();
        $thread = $em->getRepository('TerAelisCommentBundle:Thread')
            ->findOneById($id);

        if($thread == null) {
            throw $this->createNotFoundException("Thread introuvable (idThread = ".$id.")");
        }

        if($thread->getLock()) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Thread vérouillé');
        }

        $post = $thread->getPost();

        if($post == null) {
            throw $this->createNotFoundException("Post introuvable (idThread = ".$id.")");
        }

        $categorie = $post->getMainCategorie();

        if(empty($categorie)) {
            throw $this->createNotFoundException("Catégorie introuvable (idThread = ".$post->getId().")");
        }

        $path = $em->getRepository('TerAelisForumBundle:Categorie')
            ->getPath($categorie);

        $repondreSujet = $perm['repondreSujet'][$categorie->getId()];
        if (!$repondreSujet) {
            throw new AccessDeniedException("Interdiction de poster dans cette catégorie (Categorie = ".$categorie->getTitle().")");
        }

        $returnArray = array();

        $comment = new Comment();
        $comment->setBody($body);
        $form = $this->createForm(new CommentType(), $comment);

        // Le sujet est déjà envoyé
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $date = new \DateTime();
            $comment->setThread($thread);
            $comment->setCreatedAt($date);
            if ($user != null) {
                $comment->setAuthor($user);
            }
            if($form->get('previsualiser')->isClicked()) {
                $returnArray['comment'] = $comment;
            } else {
                $lastComment = $em->getRepository('TerAelisCommentBundle:Comment')
                    ->findLastByUser($user->getId());

                $lastCommentMaxTime = new \DateTime();
                $lastCommentMaxTime->modify('-10 seconds');
                if(!empty($lastComment) && $lastComment->getCreatedAt()->getTimestamp() > $lastCommentMaxTime->getTimestamp()) {
                    $returnArray['notAllowed'] = 'Votre dernier commentaire a été créé il y a trop peu de temps. Vérifiez qu\'il n\'a pas déjà été créé avant de continuer';
                    $returnArray['comment'] = $comment;
                } else {
                    $em->beginTransaction();
                    $em->persist($comment);
                    $em->flush();

                    $thread->setNumberComment($thread->getNumberComment() + 1);
                    $em->persist($thread);
                    $em->flush();

                    // On prévient les users
                    $nonVuService = $this->container->get('ter_aelis_forum.non_vu');
                    $nonVuService->updateNonVu($post, $comment);

                    $postStatistics = $this->get('ter_aelis_forum.post_statistics');
                    $postStatistics->refreshPosts(array($post));

                    $categorieIds = array();
                    $parent = $categorie;
                    while ($parent != null) {
                        $categorieIds[] = $parent->getId();
                        $parent = $parent->getParent();
                    }
                    $postStatistics->refreshCategories($categorieIds);

                    $em->commit();

                    $page = intval(1 + ($thread->getNumberComment() - 1) / $this->container->getParameter('nb_commentaires'));

                    // Puis on redirige vers la page de visualisation de cet article
                    return $this->redirect($this->generateUrl('taforum_voirSujet', array(
                            'pole' => $pole,
                            'slug' => $post->getSlug(),
                            'page' => $page
                        )) . '#last');
                }
            }
        }

        $returnArray = array_merge($returnArray, array(
            'pole' => $pole,
            'categorie' => $categorie,
            'path' => $path,
            'post' => $post,
            'thread' => $thread,
            'form' => $form->createView(),
            'user' => $user,
        ));

        return $this->render('TerAelisCommentBundle:Thread:answer.html.twig', $returnArray);
    }

    public function editAction($pole, $id) {
        $returnArray = array();
        $user = $this->getUser();
        $permService = $this->container->get('ter_aelis_forum.permission');
        $perm = $permService->getPerm($user);

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('TerAelisCommentBundle:Comment')
            ->findOneById($id);

        if($comment == null) {
            throw $this->createNotFoundException("Comment introuvable (id = ".$id.")");
        }

        $thread = $comment->getThread();

        if($thread == null) {
            throw $this->createNotFoundException("Thread introuvable (idComment = ".$id.")");
        }

        if($thread->getLock()) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Thread vérouillé');
        }

        $post = $thread->getPost();

        if($post == null) {
            throw $this->createNotFoundException("Post introuvable (idThread = ".$thread->getId().")");
        }

        $categorie = $post->getMainCategorie();

        if($categorie == null) {
            throw $this->createNotFoundException("Catégorie introuvable (idThread = ".$post->getId().")");
        }

        // On cherche les droits de la personne
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);

        $categorieId = $categorie->getId();
        if (!$permService->hasRight('moderer', $categorie->getId(), $perm)) {
            if (!$permService->hasRight('editerMessage', $categorie->getId(), $perm) || $comment->getAuthor()->getId() != $user->getId()) {
                throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour éditer les commentaires dans cette catégorie.");
            }
        }

        // Création du formulaire
        $form = $this->createForm(new CommentType, $comment);

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
                    $returnArray['comment'] = $comment;
                } else {
                    $date = new \DateTime();
                    $comment->setEditedAt($date);
                    $em->persist($comment);
                    $em->flush();

                    $page = intval(1 + $thread->getNumberComment() / $this->container->getParameter('nb_commentaires'));

                    // Puis on redirige vers la page de visualisation de cet article
                    return $this->redirect($this->generateUrl('taforum_voirSujet', array(
                        'pole' => $pole,
                        'slug' => $post->getSlug(),
                        'page' => $page
                    )));
                }
            }
        }

        return $this->render('TerAelisCommentBundle:Thread:answer.html.twig', array_merge(
            $returnArray,
            array(
                'pole' => $pole,
                'categorie' => $categorie,
                'post' => $post,
                'thread' => $thread,
                'form' => $form->createView(),
                'user' => $user,
            )
        ));
    }

    public function deleteAction($pole, $id) {
        $user = $this->getUser();
        $permService = $this->container->get('ter_aelis_forum.permission');
        $perm = $permService->getPerm($user);

        $em = $this->getDoctrine()->getManager();
        $comment = $em->getRepository('TerAelisCommentBundle:Comment')
            ->findOneById($id);

        if($comment == null) {
            throw $this->createNotFoundException("Comment introuvable (id = ".$id.")");
        }

        $thread = $comment->getThread();

        if($thread == null) {
            throw $this->createNotFoundException("Thread introuvable (idComment = ".$id.")");
        }

        if($thread->getLock()) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Thread vérouillé');
        }

        $post = $thread->getPost();

        if($post == null) {
            throw $this->createNotFoundException("Post introuvable (idThread = ".$thread->getId().")");
        }

        $categorie = $post->getMainCategorie();
        if($categorie == null) {
            throw $this->createNotFoundException("Catégorie introuvable (idThread = ".$post->getId().")");
        }

        $path = $em->getRepository('TerAelisForumBundle:Categorie')->getPath($categorie);

        if (!$permService->hasRight('moderer', $categorie->getId(), $perm)) {
            if (!$permService->hasRight('supprimerMessage', $categorie->getId(), $perm) || $comment->getAuthor()->getId() != $user->getId()) {
                throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour supprimer les commentaires dans cette catégorie.");
            } else {
                if($post->getLastComment() > $comment->getCreatedAt()) {
                    throw new AccessDeniedException("Vous n'avez pas les privilèges suffisants pour supprimer un commentaire si celui-ci a déjà eu des réponses dans cette catégorie.");
                }
            }
        }

        // Création du formulaire
        $form = $this->createFormBuilder()->getForm();

        // Le sujet est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            // On récupère les informations
            $form->bind($request);
            // Gestion de la validation du formulaire
            if ($form->isValid())
            {
                $em->beginTransaction();

                $em->remove($comment);
                $em->flush();

                $thread->setNumberComment($thread->getNumberComment() - 1);
                $em->persist($thread);
                $em->flush();

                $postStatistics = $this->get('ter_aelis_forum.post_statistics');
                $postStatistics->refreshPosts(array($post));

                $parent = $categorie;
                $categoriesId = array();
                while ($parent != null) {
                    $categoriesId[$parent->getId()] = $parent->getId();
                    $parent = $parent->getParent();
                }
                $postStatistics->refreshCategories($categoriesId);

                $em->commit();

                // Puis on redirige vers la page de visualisation de cet article
                return $this->redirect( $this->generateUrl('taforum_voirSujet', array(
                    'pole' => $pole,
                    'slug' => $post->getSlug()
                )) );
            }
        }

        return $this->render('TerAelisCommentBundle:Thread:delete.html.twig', array(
            'pole' => $pole,
            'categorie' => $categorie,
            'post' => $post,
            'thread' => $thread,
            'form' => $form->createView(),
            'user' => $user,
            'path' => $path
        ));
    }

    public function quotePostAction(Request $request, $pole, $threadId, $sujetId) {
        $sujet = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Post')
            ->findOneById($sujetId);

        $cat = $sujet->getMainCategorie();
        $perm = $this->getPerm();
        if(empty($perm) || empty($perm['voirSujet'][$cat->getId()]) || $perm['voirSujet'][$cat->getId()] != 1) {
            $sujet = null;
        }

        if (empty($sujet)) {
            $body = 'Le sujet n\'a pas été trouvé.';
            $author = '';
        } else {
            $body = '';
            $formDonnees = $sujet->getFormulaireDonnees();
            foreach($formDonnees as $f) {
                $body .= $f->getContenu();
            }
            $author = '="'.$sujet->getAuthors()->first()->getUsername().'"';
        }
        $body = '[quote'.$author.']'.$body.'[/quote]';
        return $this->answerAction($request, $pole, $threadId, $body);
    }

    public function quoteCommentAction(Request $request, $pole, $threadId, $commentId) {
        $comment = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisCommentBundle:Comment')
            ->findQuotedOneById($commentId);

        $cat = $comment->getThread()->getPost()->getMainCategorie();
        $perm = $this->getPerm();
        if(empty($perm) || empty($perm['voirSujet'][$cat->getId()]) || $perm['voirSujet'][$cat->getId()] != 1) {
            $comment = null;
        }

        if (empty($comment)) {
            $body = 'Le commentaire n\'a pas été trouvé.';
            $author = '';
        } else {
            $body = $comment->getBody();
            $author = '="'.$comment->getAuthorName().'"';
        }
        $body = '[quote'.$author.']'.$body.'[/quote]';
        return $this->answerAction($request, $pole, $threadId, $body);
    }

    public function getPerm() {
        $permService = $this->container->get('ter_aelis_forum.permission');
        $user = $this->getUser();
        $perm = $permService->getPerm($user);
        return $perm;
    }

    public function getCategorie($threadId) {
        $em = $this->getDoctrine()->getManager();

        $q = "SELECT c FROM TerAelisForumBundle:Categorie c
            JOIN TerAelisForumBundle:Post p
            WITH p.mainCategorie = c
            JOIN TerAelisCommentBundle:Thread t
            WITH t.post = p
            WHERE t.id = ".$threadId;
        $categorie = $em->createQuery($q)
            ->getSingleResult();
        return $categorie;
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