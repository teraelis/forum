<?php

namespace TerAelis\TChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NonVuTChat
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\TChatBundle\Entity\NonVuTChatRepository")
 */
class NonVuTChat
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
     * @ORM\ManyToOne(targetEntity="TerAelis\TChatBundle\Entity\Salon")
     */
    private $salon;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="nonVusTChat")
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
     * @return \TerAelis\TChatBundle\Entity\Salon
     */
    public function getSalon()
    {
        return $this->salon;
    }

    /**
     * @param \TerAelis\TChatBundle\Entity\Salon $salon
     */
    public function setSalon($salon)
    {
        $this->salon = $salon;
    }

    /**
     * @return \TerAelis\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \TerAelis\UserBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
