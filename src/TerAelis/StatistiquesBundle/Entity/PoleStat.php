<?php

namespace TerAelis\StatistiquesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PoleStat
 *
 * @ORM\Table(name="pole_stats",indexes={@ORM\Index(name="pole_idx", columns={"pole"}), @ORM\Index(name="date_idx", columns={"date"})})
 * @ORM\Entity(repositoryClass="TerAelis\StatistiquesBundle\Entity\PoleStatRepository")
 */
class PoleStat
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
     * @ORM\Column(name="pole", type="string", length=50)
     */
    private $pole;

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
     * @return PoleStat
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
     * @return PoleStat
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
     * @return PoleStat
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
     * @return string
     */
    public function getPole()
    {
        return $this->pole;
    }

    /**
     * @param string $pole
     * @return PoleStat
     */
    public function setPole($pole)
    {
        $this->pole = $pole;
        return $this;
    }
}

