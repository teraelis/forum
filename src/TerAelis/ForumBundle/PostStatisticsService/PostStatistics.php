<?php

namespace TerAelis\ForumBundle\PostStatisticsService;

use Doctrine\ORM\EntityManager;
use TerAelis\ForumBundle\Entity\Categorie;
use TerAelis\ForumBundle\Entity\Post;

class PostStatistics {
    private $postRepo;
    private $categoryRepo;

    public function __construct(EntityManager $em)
    {
        $this->postRepo = $em->getRepository('TerAelisForumBundle:Post');
        $this->categoryRepo = $em->getRepository('TerAelisForumBundle:Categorie');
        $this->em = $em;
    }

    public function refreshCategories($arrayCategoryId = array())
    {
        if(!empty($arrayCategoryId)) {
            $categories = $this->categoryRepo
                ->findByArrayId($arrayCategoryId);
            if($categories == null || count($categories) <= 0) {
                return null;
            }
        } else {
            $categories = array();
        }

        $fullCategories = $this->categoryRepo
            ->findFullCategories($categories);
        if($fullCategories == null || count($fullCategories) <= 0) {
            return null;
        }

        $fullCategories = $this->refreshLastInCategories($fullCategories);
        $fullCategories = $this->refreshStatsCategories($fullCategories);

        foreach($fullCategories as $cat) {
            $this->em->persist($cat);
        }
        $this->em->flush();
    }

    /**
     * @param $stats
     * @param Categorie[] $categories
     */
    public function setNewStats($stats, &$categories) {
        foreach($categories as $key => $cat) {
            $children = $cat->getChildren();
            $stat = $stats[$cat->getId()];
            foreach($children as $child) {
                $stat['nb_post'] += $child->getNumberPost();
                $stat['nb_comment'] += $child->getNumberComment();
            }
            $cat->setNumberPost($stat['nb_post']);
            $cat->setNumberComment($stat['nb_comment']);

            $categories[$key] = $cat;
        }
    }

    public function refreshPosts($fullPosts = array())
    {
        if(empty($fullPosts)) {
            $fullPosts = $this->postRepo->findFullPosts();
        }
        $fullPosts = $this->refreshStatsPosts($fullPosts);
        $fullPosts = $this->refreshLastInPosts($fullPosts);

        foreach($fullPosts as $p) {
            $this->em->persist($p);
        }
        $this->em->flush();
    }

    /**
     * @param Post[] $fullPosts
     * @return Post[]
     */
    public function refreshLastInPosts($fullPosts) {
        $postsList = [];
        foreach($fullPosts as $p) {
            $comment = $this->em->getRepository('TerAelisCommentBundle:Comment')
                ->findLastFromPost($p);
            if(empty($comment)) {
                $p->setLastAuthor($p->getAuthors()->first());
                $p->setLastComment($p->getDatePublication());
            } else {
                $p->setLastAuthor($comment->getAuthor());
                $p->setLastComment($comment->getCreatedAt());
            }

            $postsList[] = $p;
        }

        return $postsList;
    }

    /**
     * @param Categorie[] $fullCategories
     * @return Categorie[]
     */
    public function refreshLastInCategories($fullCategories) {
        $result = [];
        foreach($fullCategories as $c) {
            $post = $this->postRepo
                ->findLastByCategorie($c);
            if(empty($post)) {
                $c->setLastPost(null, true);
            } else {
                $c->setLastPost($post, true);
            }
            $result[] = $c;
        }

        return $result;
    }

    /**
     * @param Post[] $fullPosts
     * @return array
     */
    public function refreshStatsPosts($fullPosts)
    {
        $threadsList = [];
        $postsList = [];
        foreach ($fullPosts as $p) {
            $threads = $p->getThreads();
            foreach ($threads as $t) {
                $threadsList[$t->getId()] = array(
                    'id' => $t->getId(),
                    'thread' => $t,
                    'post_id' => $p->getId(),
                    'number_comment' => 0,
                    'last_comment' => null,
                );
                $postsList[$p->getId()] = $p;
            }
            $p->setNumberComment(0);
        }
        unset($fullPosts);

        $stats = $this->em->getRepository('TerAelisCommentBundle:Comment')
            ->findStats(array_keys($threadsList));
        foreach ($stats as $s) {
            $thread = $threadsList[$s['id']];
            $thread['number_comment'] += $s['nb_comment'];
            $thread['thread']->setNumberComment($thread['number_comment']);
            $threadsList[$s['id']] = $thread;

            $post = $postsList[$thread['post_id']];
            $totalCommentsPost = $post->getNumberComment() + $thread['number_comment'];
            $post->setNumberComment($totalCommentsPost);
            $postsList[$thread['post_id']] = $post;
        }

        foreach($threadsList as $t) {
            $this->em->persist($t['thread']);
        }

        unset($stats);

        return $postsList;
    }

    /**
     * @param $fullCategories
     * @return mixed
     */
    public function refreshStatsCategories($fullCategories)
    {
        $stats = array();
        foreach ($fullCategories as $c) {
            $stats[$c->getId()] = array(
                'id' => $c->getId(),
                'c' => $c,
                'nb_post' => 0,
                'nb_comment' => 0,
            );
        }
        $individualStats = $this->postRepo
            ->getStatsByCategories($fullCategories);
        foreach ($individualStats as $stat) {
            $stats[$stat['id']] = $stat;
        }

        $this->setNewStats($stats, $fullCategories);
        return $fullCategories;
    }

}