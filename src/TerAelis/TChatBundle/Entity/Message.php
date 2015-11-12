<?php

namespace TerAelis\TChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TerAelis\ForumBundle\Twig\Extensions\PostExtension;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\TChatBundle\Entity\MessageRepository")
 */
class Message
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
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\TChatBundle\Entity\Salon", inversedBy="messages")
     */
    private $salon;

    /**
     * @ORM\Column(name="hide", type="boolean")
     */
    private $hide = false;

    /**
     * @var \DateTime()
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * Set message
     *
     * @param string $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set user
     *
     * @param \TerAelis\UserBundle\Entity\User $user
     * @return Message
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
     * Set salon
     *
     * @param \TerAelis\TchatBundle\Entity\Salon $salon
     * @return Message
     */
    public function setSalon(\TerAelis\TchatBundle\Entity\Salon $salon = null)
    {
        $this->salon = $salon;

        return $this;
    }

    /**
     * Get salon
     *
     * @return \TerAelis\TchatBundle\Entity\Salon 
     */
    public function getSalon()
    {
        return $this->salon;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Message
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
     * Set hide
     *
     * @param boolean $hide
     * @return Message
     */
    public function setHide($hide)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Get hide
     *
     * @return boolean 
     */
    public function getHide()
    {
        return $this->hide;
    }

    public function jsonSerialize(PostExtension $postExtension) {
        return json_encode(array(
            'user' => $postExtension->showUsername($this->getUser()),
            'date' => $this->getCreatedAt(),
            'mod' => false,
            'hide' => $this->getHide(),
            'id' => $this->getId(),
            'message' => $this->getMessage()
        ));
    }
}
