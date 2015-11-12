<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sondage
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Sondage
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
     * @ORM\Column(name="question", type="string", length=255)
     */
    private $question;

    /**
     * @var date
     * @ORM\Column(name="date_debut", type="date")
     */
    private $dateDebut;

    /**
     * @var date
     * @ORM\Column(name="date_fin", type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @var boolean
     * @ORM\Column(name="choix_multiples", type="boolean")
     */
    private $choixMultiples;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\Choix", mappedBy="sondage", cascade={"persist", "remove"})
     */
    private $choix;

    /**
     * @ORM\OneToOne(targetEntity="TerAelis\ForumBundle\Entity\Post", mappedBy="sondage", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $post;

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
     * Set question
     *
     * @param string $question
     * @return Sondage
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return string 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set choice
     *
     * @param \TerAelis\ForumBundle\Entity\Choix $choix
     * @return Sondage
     */
    public function setChoix($choix)
    {
        $this->choix = $choix;

        return $this;
    }

    /**
     * Get choice
     *
     * @return \stdClass 
     */
    public function getChoix()
    {
        return $this->choix;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dateDebut = new \DateTime();
        $this->choix = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return Sondage
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Sondage
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set choixMultiples
     *
     * @param boolean $choixMultiples
     * @return Sondage
     */
    public function setChoixMultiples($choixMultiples)
    {
        $this->choixMultiples = $choixMultiples;

        return $this;
    }

    /**
     * Get choixMultiples
     *
     * @return boolean 
     */
    public function getChoixMultiples()
    {
        return $this->choixMultiples;
    }

    /**
     * Add choix
     *
     * @param \TerAelis\ForumBundle\Entity\Choix $choix
     * @return Sondage
     */
    public function addChoix(\TerAelis\ForumBundle\Entity\Choix $choix)
    {
        $this->choix[] = $choix;

        return $this;
    }

    /**
     * Remove choix
     *
     * @param \TerAelis\ForumBundle\Entity\Choix $choix
     */
    public function removeChoix(\TerAelis\ForumBundle\Entity\Choix $choix)
    {
        $this->choix->removeElement($choix);
    }

    /**
     * Set post
     *
     * @param \TerAelis\ForumBundle\Entity\Post $post
     * @return Sondage
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
}
