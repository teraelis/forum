<?php

namespace TerAelis\StatistiquesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stats
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\StatistiquesBundle\Entity\StatsRepository")
 */
class Stats
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
     * @var integer
     *
     * @ORM\Column(name="nbMessages", type="integer")
     */
    private $nbMessages;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbSujets", type="integer")
     */
    private $nbSujets;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbMembres", type="integer")
     */
    private $nbMembres;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_update", type="datetime")
     */
    private $lastUpdate;

    function __construct()
    {
        $this->lastUpdate = new \DateTime();
    }


    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
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
     * Set nbMessages
     *
     * @param integer $nbMessages
     * @return Stats
     */
    public function setNbMessages($nbMessages)
    {
        $this->nbMessages = $nbMessages;

        return $this;
    }

    /**
     * Get nbMessages
     *
     * @return integer 
     */
    public function getNbMessages()
    {
        return $this->nbMessages;
    }

    /**
     * Set nbSujets
     *
     * @param integer $nbSujets
     * @return Stats
     */
    public function setNbSujets($nbSujets)
    {
        $this->nbSujets = $nbSujets;

        return $this;
    }

    /**
     * Get nbSujets
     *
     * @return integer 
     */
    public function getNbSujets()
    {
        return $this->nbSujets;
    }

    /**
     * Set nbMembres
     *
     * @param integer $nbMembres
     * @return Stats
     */
    public function setNbMembres($nbMembres)
    {
        $this->nbMembres = $nbMembres;

        return $this;
    }

    /**
     * Get nbMembres
     *
     * @return integer 
     */
    public function getNbMembres()
    {
        return $this->nbMembres;
    }
}
