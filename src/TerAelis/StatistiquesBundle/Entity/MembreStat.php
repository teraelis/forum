<?php

namespace TerAelis\StatistiquesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TerAelis\UserBundle\Entity\User;

/**
 * MembreStat
 *
 * @ORM\Table(name="membre_stats",indexes={@ORM\Index(name="membre_idx", columns={"membre"}), @ORM\Index(name="date_idx", columns={"date"})})
 * @ORM\Entity(repositoryClass="TerAelis\StatistiquesBundle\Entity\MembreStatRepository")
 */
class MembreStat
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="cascade", name="membre")
     */
    private $membre;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbSujet", type="integer")
     */
    private $nbSujet;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbCommentaire", type="integer")
     */
    private $nbCommentaire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * Set nbSujet
     *
     * @param integer $nbSujet
     *
     * @return MembreStat
     */
    public function setNbSujet($nbSujet)
    {
        $this->nbSujet = $nbSujet;

        return $this;
    }

    /**
     * Get nbSujet
     *
     * @return integer
     */
    public function getNbSujet()
    {
        return $this->nbSujet;
    }

    /**
     * Set nbCommentaire
     *
     * @param integer $nbCommentaire
     *
     * @return MembreStat
     */
    public function setNbCommentaire($nbCommentaire)
    {
        $this->nbCommentaire = $nbCommentaire;

        return $this;
    }

    /**
     * Get nbCommentaire
     *
     * @return integer
     */
    public function getNbCommentaire()
    {
        return $this->nbCommentaire;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return MembreStat
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return User
     */
    public function getMembre()
    {
        return $this->membre;
    }

    /**
     * @param User $membre
     * @return MembreStat
     */
    public function setMembre($membre)
    {
        $this->membre = $membre;
        return $this;
    }
}

