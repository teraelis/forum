<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission
 *
 * @ORM\Table(name="permission")
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\PermissionRepository")
 */
class Permission
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
     * @var boolean
     *
     * @ORM\Column(name="voir_categorie", type="boolean")
     */
    private $voirCategorie;

    /**
     * @var boolean
     *
     * @ORM\Column(name="voir_sujet", type="boolean")
     */
    private $voirSujet;

    /**
     * @var boolean
     *
     * @ORM\Column(name="creer_sujet", type="boolean")
     */
    private $creerSujet;

    /**
     * @var boolean
     *
     * @ORM\Column(name="repondre_sujet", type="boolean")
     */
    private $repondreSujet;

    /**
     * @var boolean
     *
     * @ORM\Column(name="editer_message", type="boolean")
     */
    private $editerMessage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="supprimer_message", type="boolean")
     */
    private $supprimerMessage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="creer_sondage", type="boolean")
     */
    private $creerSondage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="voter", type="boolean")
     */
    private $voter;

    /**
     * @var boolean
     *
     * @ORM\Column(name="creer_special", type="boolean")
     */
    private $creerSpecial;

    /**
     * @var boolean
     *
     * @ORM\Column(name="moderer", type="boolean")
     */
    private $moderer;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Categorie", cascade={"persist"})
     * @ORM\JoinColumn(name="categorie_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $categorie;


    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\Group", cascade={"persist"}, inversedBy="permission")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $group;

    function __construct($categorie, $group)
    {
        $this->categorie = $categorie;
        $this->group = $group;
        $this->voirCategorie = false;
        $this->voirSujet = false;
        $this->creerSujet = false;
        $this->creerSpecial = false;
        $this->repondreSujet = false;
        $this->editerMessage = false;
        $this->supprimerMessage = false;
        $this->creerSondage = false;
        $this->voter = false;
        $this->moderer = false;
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
     * Set voirCategorie
     *
     * @param boolean $voirCategorie
     * @return Permission
     */
    public function setVoirCategorie($voirCategorie)
    {
        $this->voirCategorie = $voirCategorie;

        return $this;
    }

    /**
     * Get voirCategorie
     *
     * @return boolean 
     */
    public function getVoirCategorie()
    {
        return $this->voirCategorie;
    }

    /**
     * Set voirSujet
     *
     * @param boolean $voirSujet
     * @return Permission
     */
    public function setVoirSujet($voirSujet)
    {
        $this->voirSujet = $voirSujet;

        return $this;
    }

    /**
     * Get voirSujet
     *
     * @return boolean 
     */
    public function getVoirSujet()
    {
        return $this->voirSujet;
    }

    /**
     * Set creerSujet
     *
     * @param boolean $creerSujet
     * @return Permission
     */
    public function setCreerSujet($creerSujet)
    {
        $this->creerSujet = $creerSujet;

        return $this;
    }

    /**
     * Get creerSujet
     *
     * @return boolean 
     */
    public function getCreerSujet()
    {
        return $this->creerSujet;
    }

    /**
     * Set repondreSujet
     *
     * @param boolean $repondreSujet
     * @return Permission
     */
    public function setRepondreSujet($repondreSujet)
    {
        $this->repondreSujet = $repondreSujet;

        return $this;
    }

    /**
     * Get repondreSujet
     *
     * @return boolean 
     */
    public function getRepondreSujet()
    {
        return $this->repondreSujet;
    }

    /**
     * Set editerMessage
     *
     * @param boolean $editerMessage
     * @return Permission
     */
    public function setEditerMessage($editerMessage)
    {
        $this->editerMessage = $editerMessage;

        return $this;
    }

    /**
     * Get editerMessage
     *
     * @return boolean 
     */
    public function getEditerMessage()
    {
        return $this->editerMessage;
    }

    /**
     * Set supprimerMessage
     *
     * @param boolean $supprimerMessage
     * @return Permission
     */
    public function setSupprimerMessage($supprimerMessage)
    {
        $this->supprimerMessage = $supprimerMessage;

        return $this;
    }

    /**
     * Get supprimerMessage
     *
     * @return boolean 
     */
    public function getSupprimerMessage()
    {
        return $this->supprimerMessage;
    }

    /**
     * Set creerSondage
     *
     * @param boolean $creerSondage
     * @return Permission
     */
    public function setCreerSondage($creerSondage)
    {
        $this->creerSondage = $creerSondage;

        return $this;
    }

    /**
     * Get creerSondage
     *
     * @return boolean 
     */
    public function getCreerSondage()
    {
        return $this->creerSondage;
    }

    /**
     * Set voter
     *
     * @param boolean $voter
     * @return Permission
     */
    public function setVoter($voter)
    {
        $this->voter = $voter;

        return $this;
    }

    /**
     * Get voter
     *
     * @return boolean 
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * Set creerSpecial
     *
     * @param boolean $creerSpecial
     * @return Permission
     */
    public function setCreerSpecial($creerSpecial)
    {
        $this->creerSpecial = $creerSpecial;

        return $this;
    }

    /**
     * Get creerSpecial
     *
     * @return boolean 
     */
    public function getCreerSpecial()
    {
        return $this->creerSpecial;
    }

    /**
     * Set moderer
     *
     * @param boolean $moderer
     * @return Permission
     */
    public function setModerer($moderer)
    {
        $this->moderer = $moderer;

        return $this;
    }

    /**
     * Get moderer
     *
     * @return boolean 
     */
    public function getModerer()
    {
        return $this->moderer;
    }

    /**
     * Set categorie
     *
     * @param \stdClass $categorie
     * @return Permission
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \stdClass 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set group
     *
     * @param \TerAelis\UserBundle\Entity\Group $group
     * @return Permission
     */
    public function setGroup(\TerAelis\UserBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \TerAelis\UserBundle\Entity\Group 
     */
    public function getGroup()
    {
        return $this->group;
    }
}
