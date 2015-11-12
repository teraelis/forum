<?php

namespace TerAelis\StatistiquesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * View
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\StatistiquesBundle\Entity\ViewsRepository")
 */
class View
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
     * @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post", cascade={"persist"}, inversedBy="views")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $post;

    /**
     * @var integer
     * @ORM\Column(name="count", type="integer")
     */
    private $count = 1;

    function __construct($ip, $post)
    {
        $this->ip = $ip;
        $this->post = $post;
        $this->date = new \DateTime();
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
     * Set ip
     *
     * @param string $ip
     * @return View
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return View
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
     * Set post
     *
     * @param \TerAelis\ForumBundle\Entity\Post $post
     * @return View
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

    /**
     * Set count
     *
     * @param integer $count
     * @return View
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }
}
