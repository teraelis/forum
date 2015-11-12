<?php

namespace TerAelis\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Thread as BaseThread;

/**
 * Thread
 *
 * @ORM\Table(name="thread")
 * @ORM\Entity(repositoryClass="TerAelis\CommentBundle\Entity\ThreadRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Thread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post", cascade={"persist"}, inversedBy="threads")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $post;

    /**
     * Thread locked
     *
     * @var Boolean
     * @ORM\Column(name="locked", type="boolean")
     */
    private $lock = false;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\CommentBundle\Entity\Comment", mappedBy="thread")
     * @ORM\JoinColumn(nullable=true)
     */
    private $comments;

    /**
     * Last comment
     *
     * @var \DateTime
     * @ORM\Column(name="last_comment", type="datetime", nullable=true)
     */
    private $lastComment;

    /**
     * Number comment
     *
     * @var integer
     * @ORM\Column(name="num_comment", type="integer")
     */
    private $numberComment = 0;

    /**
     * Set id
     *
     * @param string $id
     * @return Thread
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set post
     *
     * @param \TerAelis\ForumBundle\Entity\Post $post
     * @return Thread
     */
    public function setPost(\TerAelis\ForumBundle\Entity\Post $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \TerAelis\ForumBundle\Entity\Post 
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set lock
     *
     * @param boolean $lock
     * @return Thread
     */
    public function setLock($lock)
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * Get lock
     *
     * @return boolean 
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * Set lastComment
     *
     * @param \DateTime $lastComment
     * @return Thread
     */
    public function setLastComment($lastComment = null)
    {
        $this->lastComment = $lastComment;

        return $this;
    }

    /**
     * Get lastComment
     *
     * @return \DateTime 
     */
    public function getLastComment()
    {
        return $this->lastComment;
    }

    /**
     * Set numberComment
     *
     * @param integer $numberComment
     * @return Thread
     */
    public function setNumberComment($numberComment)
    {
        $this->numberComment = $numberComment;

        return $this;
    }

    /**
     * Get numberComment
     *
     * @return integer 
     */
    public function getNumberComment()
    {
        return $this->numberComment;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add comments
     *
     * @param \TerAelis\CommentBundle\Entity\Comment $comments
     * @return Thread
     */
    public function addComment(\TerAelis\CommentBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \TerAelis\CommentBundle\Entity\Comment $comments
     */
    public function removeComment(\TerAelis\CommentBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }
}
