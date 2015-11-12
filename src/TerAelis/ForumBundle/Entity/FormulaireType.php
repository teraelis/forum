<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormulairePost
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\FormulairePostRepository")
 */
class FormulaireType
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
     * @ORM\Column(name="nom", type="string", length=128)
     */
    private $nom;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(name="valdefault", type="text", nullable=true)
     */
    private $default;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Categorie", inversedBy="formulaire")
     * @ORM\JoinColumn(nullable=false, name="categorie_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $categorie;

    /**
     * @ORM\Column(name="ordre", type="integer")
     */
    private $ordre;

    /**
     * @var boolean
     * @ORM\Column(name="is_title_visible", type="boolean")
     */
    private $isTitleVisible = false;

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
     * @return FormulairePost
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
     * Set description
     *
     * @param string $description
     * @return FormulairePost
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     * @return FormulairePost
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set categorie
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $categorie
     * @return FormulairePost
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

    /**
     * Set default
     *
     * @param string $default
     * @return FormulaireType
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get default
     *
     * @return string 
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return boolean
     */
    public function isIsTitleVisible() {
        return $this->isTitleVisible;
    }

    /**
     * @param boolean $isTitleVisible
     */
    public function setIsTitleVisible($isTitleVisible) {
        $this->isTitleVisible = $isTitleVisible;
    }
}
