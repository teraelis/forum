<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FollowedPost
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\FollowedPostRepository")
 */
class FollowedPost
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
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="follow")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

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
     * @return FollowedPost
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
     * @return FollowedPost
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
}
