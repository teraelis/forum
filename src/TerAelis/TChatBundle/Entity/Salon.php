<?php

namespace TerAelis\TChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TerAelis\UserBundle\Entity\User;

/**
 * Salon
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\TChatBundle\Entity\SalonRepository")
 */
class Salon
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime()
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime()
     *
     * @ORM\Column(name="lastUpdate", type="datetime")
     */
    private $lastUpdate;

    /**
     * @ORM\JoinTable(name="salon_user")
     * @ORM\ManyToMany(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="salons")
     */
    private $users;

    /**
     * @ORM\JoinTable(name="salon_mod")
     * @ORM\ManyToMany(targetEntity="TerAelis\UserBundle\Entity\User")
     */
    private $mods;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\TChatBundle\Entity\Message", mappedBy="salon", cascade={"remove"})
     */
    private $messages;

    /**
     * @var boolean
     * @ORM\Column(name="private", type="boolean")
     */
    private $private = true;

    function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lastUpdate = new \DateTime();
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
     * Set name
     *
     * @param string $name
     * @return Salon
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Salon
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
     * Add users
     *
     * @param User $users
     * @return Salon
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Reset users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function resetUsers() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection;

        return $this->users;
    }

    /**
     * Add mods
     *
     * @param User $mods
     * @return Salon
     */
    public function addMod(User $mods)
    {
        $this->mods[] = $mods;

        return $this;
    }

    /**
     * Remove mods
     *
     * @param User $mods
     */
    public function removeMod(User $mods)
    {
        $this->mods->removeElement($mods);
    }

    /**
     * Get mods
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMods()
    {
        return $this->mods;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return Salon
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Add messages
     *
     * @param \TerAelis\TChatBundle\Entity\Message $messages
     * @return Salon
     */
    public function addMessage(\TerAelis\TChatBundle\Entity\Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \TerAelis\TChatBundle\Entity\Message $messages
     */
    public function removeMessage(\TerAelis\TChatBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return mixed
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param mixed $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }


}
