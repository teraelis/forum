<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NonVu
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\NonVuRepository")
 */
class NonVu
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="nonVus")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\CommentBundle\Entity\Comment")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $comment;

    /**
     * @ORM\Column(name="state", type="integer")
     */
    private $state;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set post
     *
     * @param \TerAelis\ForumBundle\Entity\Post $post
     * @return NonVu
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
     * Set user
     *
     * @param \TerAelis\UserBundle\Entity\User $user
     * @return NonVu
     */
    public function setUser(\TerAelis\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \TerAelis\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set comment
     *
     * @param \TerAelis\CommentBundle\Entity\Comment $comment
     * @return NonVu
     */
    public function setComment(\TerAelis\CommentBundle\Entity\Comment $comment = null)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \TerAelis\CommentBundle\Entity\Comment 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return NonVu
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer 
     */
    public function getState()
    {
        return $this->state;
    }
}
