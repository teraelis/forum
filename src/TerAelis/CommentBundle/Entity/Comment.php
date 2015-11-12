<?php

namespace TerAelis\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as BaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use TerAelis\UserBundle\Entity\User;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="TerAelis\CommentBundle\Entity\CommentRepository")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Thread of this comment
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="TerAelis\CommentBundle\Entity\Thread", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $thread;

    /**
     * Author of the comment
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $author;

    /**
     * Parent comment id
     *
     * @var Comment
     * @ORM\ManyToOne(targetEntity="TerAelis\CommentBundle\Entity\Comment", cascade={"persist"})
     */
    protected $parent;

    /**
     * depth
     *
     * @var integer
     * @ORM\Column(name="depth", type="integer")
     */
    protected $depth = 0;

    /**
     * Comment text
     *
     * @var string
     * @ORM\Column(name="body", type="text")
     */
    protected $body;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_publication", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_edition", type="datetime", nullable=true)
     */
    protected $editedAt;

    /**
     * @var boolean
     * @ORM\Column(name="hidden", type="boolean")
     */
    protected $hidden = false;

    /**
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getAuthorName()
    {
        if (null === $this->getAuthor()) {
            return 'Anonymous';
        }

        return $this->getAuthor()->getUsername();
    }

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
     * Set thread
     *
     * @param \TerAelis\CommentBundle\Entity\Thread $thread
     * @return Comment
     */
    public function setThread(\TerAelis\CommentBundle\Entity\Thread $thread = null)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return \TerAelis\CommentBundle\Entity\Thread 
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Comment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set editedAt
     *
     * @param \DateTime $editedAt
     * @return Comment
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime 
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     * @return Comment
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean 
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set parent
     *
     * @param \TerAelis\CommentBundle\Entity\Comment $parent
     * @return Comment
     */
    public function setParent(\TerAelis\CommentBundle\Entity\Comment $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \TerAelis\CommentBundle\Entity\Comment 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set depth
     *
     * @param integer $depth
     * @return Comment
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth
     *
     * @return integer 
     */
    public function getDepth()
    {
        return $this->depth;
    }
}
