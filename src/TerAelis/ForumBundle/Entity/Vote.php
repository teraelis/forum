<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as UniqueEntity;


/**
 * Vote
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\VoteRepository")
 * @UniqueEntity({"sondage", "user"})
 */
class Vote
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
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", cascade={"persist"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Sondage", cascade={"persist"})
     */
    private $sondage;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Choix", cascade={"persist"})
     */
    private $choix;

    function __construct($user, $sondage)
    {
        $this->sondage = $sondage;
        $this->user = $user;
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
     * Set user
     *
     * @param \TerAelis\UserBundle\Entity\User $user
     * @return Vote
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
     * Set sondage
     *
     * @param \TerAelis\ForumBundle\Entity\Sondage $sondage
     * @return Vote
     */
    public function setSondage(\TerAelis\ForumBundle\Entity\Sondage $sondage = null)
    {
        $this->sondage = $sondage;

        return $this;
    }

    /**
     * Get sondage
     *
     * @return \TerAelis\ForumBundle\Entity\Sondage 
     */
    public function getSondage()
    {
        return $this->sondage;
    }

    /**
     * Set choix
     *
     * @param \TerAelis\ForumBundle\Entity\Choix $choix
     * @return Vote
     */
    public function setChoix(\TerAelis\ForumBundle\Entity\Choix $choix = null)
    {
        $this->choix = $choix;

        return $this;
    }

    /**
     * Get choix
     *
     * @return \TerAelis\ForumBundle\Entity\Choix 
     */
    public function getChoix()
    {
        return $this->choix;
    }
}
