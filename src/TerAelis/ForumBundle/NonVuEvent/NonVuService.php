<?php
namespace TerAelis\ForumBundle\NonVuEvent;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TerAelis\CommentBundle\Entity\Comment;
use TerAelis\CommentBundle\Entity\Thread;
use TerAelis\ForumBundle\Entity\NonVu;
use TerAelis\ForumBundle\Entity\NonVuRepository;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\UserBundle\Entity\User;
use TerAelis\UserBundle\Entity\UserRepository;

/**
 * Class NonVuListener
 * @package TerAelis\ForumBundle\NonVuEvent
 * Permet de prévenir tous les utilisateurs du nouveau post
 */
class NonVuService
{
    private $userRepo;
    private $em;
    private $nonVuRepo;
    private $commentRepo;
    const VU = 0;
    const NON_VU = 1;
    const NON_VU_AND_QUOTED = 2;

    public function __construct(EntityManager $em)
    {
        $this->userRepo = $em->getRepository('TerAelisUserBundle:User');
        $this->nonVuRepo = $em->getRepository('TerAelisForumBundle:NonVu');
        $this->commentRepo = $em->getRepository('TerAelisCommentBundle:Comment');
        $this->em = $em;
    }



    /**
     * Update de la vue
     * @param User $user User affecté
     * @param Post $post Post affecté
     * @param Comment $comment Dernier commentaire vu
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteNonVu(User $user = null, Post $post = null, Comment $comment = null) {
        if ($post == null) {
            $nonVu = $this->nonVuRepo->findBy(array('user' => $user));
            foreach($nonVu as $nv) {
                $this->em->remove($nv);
                $this->em->flush();
            }
        } else if($user == null) {
            $nonVu = $this->nonVuRepo->findBy(array('post' => $post));
            foreach($nonVu as $nv) {
                $this->em->remove($nv);
                $this->em->flush();
            }
        } else {
            // On cherche s'il existe dans les NonVu
            $nonVu = $this->nonVuRepo->findUserPost($user, $post);
            if($nonVu != null) {
                foreach ($nonVu as $nv) {
                    $this->em->remove($nv);
                }
                $this->em->flush();
            }
        }
    }

    public function updateNonVu(Post $post, Comment $comment = null)
    {
        $users = $this->userRepo->findAll();
        if($comment == null) {
            $quotedUsersId = $this->getQuotedUsersPost($post);
            foreach($users as $u) {
                $nonVu = new NonVu();
                $nonVu->setUser($u);
                $nonVu->setPost($post);
                if(in_array($u->getId(), $quotedUsersId)) {
                    $nonVu->setState(self::NON_VU_AND_QUOTED);
                } else {
                    $nonVu->setState(self::NON_VU);
                }
                $this->em->persist($nonVu);
            }
        } else {
            $quotedUsersId = $this->getQuotedUsersComment($comment);

            $nonVus = $this->nonVuRepo->findByPost($post->getId());
            $userNotToNotify = array();
            foreach($nonVus as $nv) {
                $userId = $nv->getUser()->getId();
                $userNotToNotify[] = $userId;
            }

            foreach($users as $u) {
                if(!in_array($u->getId(), $userNotToNotify)) {
                    $nonVu = new NonVu();
                    $nonVu->setUser($u);
                    $nonVu->setPost($post);
                    $nonVu->setComment($comment);
                    if (in_array($u->getId(), $quotedUsersId)) {
                        $nonVu->setState(self::NON_VU_AND_QUOTED);
                    } else {
                        $nonVu->setState(self::NON_VU);
                    }
                    $this->em->persist($nonVu);
                }
            }
        }
        $this->em->flush();
    }

    protected function getQuotedUsersPost(Post $post) {
        $contents = $post->getFormulaireDonnees();
        $arrayUserId = [];
        foreach($contents as $c) {
            preg_match_all('#\[quote="([0-9]+)"\].*[/quote]#', $c->getContenu(), $matches);
            if(isset($matches['1'])) {
                foreach($matches['1'] as $q) {
                    $arrayUserId[] = $q;
                }
            }
        }
        return $arrayUserId;
    }

    protected function getQuotedUsersComment(Comment $comment) {
        $body = $comment->getBody();
        $arrayUserId = [];
        preg_match_all('#\[quote="([0-9]+)"\].*[/quote]#', $body, $matches);
        if(isset($matches['1'])) {
            foreach($matches['1'] as $q) {
                $arrayUserId[] = $q;
            }
        }
        return $arrayUserId;
    }
}