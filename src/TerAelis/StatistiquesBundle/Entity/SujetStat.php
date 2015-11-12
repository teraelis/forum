<?php

namespace TerAelis\StatistiquesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use TerAelis\ForumBundle\Entity\Post;

/**
 * SujetStat
 *
 * @ORM\Table(name="sujet_stats",indexes={@ORM\Index(name="sujet_idx", columns={"sujet"}), @ORM\Index(name="date_idx", columns={"date"})})
 * @ORM\Entity(repositoryClass="TerAelis\StatistiquesBundle\Entity\SujetStatRepository")
 */
class SujetStat
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
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post")
     * @ORM\JoinColumn(onDelete="CASCADE", name="sujet")
     */
    private $sujet;

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
     * Set nbCommentaire
     *
     * @param integer $nbCommentaire
     *
     * @return SujetStat
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
     * @return SujetStat
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
     * @return Post
     */
    public function getSujet()
    {
        return $this->sujet;
    }

    /**
     * @param Post $sujet
     * @return SujetStat
     */
    public function setSujet($sujet)
    {
        $this->sujet = $sujet;
        return $this;
    }
}

