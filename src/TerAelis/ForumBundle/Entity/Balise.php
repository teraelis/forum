<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Balise
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\BaliseRepository")
 */
class Balise
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="court", type="string", length=10)
     */
    private $court;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Categorie", inversedBy="balise")
     * @ORM\JoinColumn(nullable=false, name="categorie_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $categorie;


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
     * Set nom
     *
     * @param string $nom
     * @return Balise
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set court
     *
     * @param string $court
     * @return Balise
     */
    public function setCourt($court)
    {
        $this->court = $court;

        return $this;
    }

    /**
     * Get court
     *
     * @return string 
     */
    public function getCourt()
    {
        return $this->court;
    }

    /**
     * Set categorie
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $categorie
     * @return Balise
     */
    public function setCategorie(\TerAelis\ForumBundle\Entity\Categorie $categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \TerAelis\ForumBundle\Entity\Categorie 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    function __toString()
    {
        return '[' . $this->getCourt() . '] ' . $this->getNom();
    }


}
