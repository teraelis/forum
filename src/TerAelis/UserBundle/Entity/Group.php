<?php

namespace TerAelis\UserBundle\Entity;

use FOS\UserBundle\Entity\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_group")
 * @ORM\Entity(repositoryClass="TerAelis\UserBundle\Entity\GroupRepository")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\UserBundle\Entity\UserRole", cascade={"remove"}, mappedBy="groupe")
     */
    protected $userRoles;


    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\Permission", mappedBy="group")
     */
    protected $permission;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $invisible = false;

    /**
     * @ORM\Column(type="string", nullable=true, length=7)
     * @Assert\Length(
     *           min=7,
     *           max=7
     * )
     * @Assert\Regex(
     *           pattern= "/^#[0-9A-Fa-f]{6}$/",
     *           match=   true,
     *           message= "Ce champ doit contenir une valeur hexadecimal de type : #a8d630"
     * )
     */
    protected $couleur = "#000000";

    /**
     * @ORM\Column(type="string", nullable=true, length=7)
     * @Assert\Length(
     *           min=7,
     *           max=7
     * )
     * @Assert\Regex(
     *           pattern= "/^#[0-9A-Fa-f]{6}$/",
     *           match=   true,
     *           message= "Ce champ doit contenir une valeur hexadecimal de type : #a8d630"
     * )
     */
    protected $colorLitte = "#000000";

    /**
     * @ORM\Column(type="string", nullable=true, length=7)
     * @Assert\Length(
     *           min=7,
     *           max=7
     * )
     * @Assert\Regex(
     *           pattern= "/^#[0-9A-Fa-f]{6}$/",
     *           match=   true,
     *           message= "Ce champ doit contenir une valeur hexadecimal de type : #a8d630"
     * )
     */
    protected $colorGfx = "#ffffff";

    /**
     * @ORM\Column(type="string", nullable=true, length=7)
     * @Assert\Length(
     *           min=7,
     *           max=7
     * )
     * @Assert\Regex(
     *           pattern= "/^#[0-9A-Fa-f]{6}$/",
     *           match=   true,
     *           message= "Ce champ doit contenir une valeur hexadecimal de type : #a8d630"
     * )
     */
    protected $colorRp = "#ffffff";

    /**
     * @ORM\Column(type="integer")
     */
    protected $ordre = 0;

    /**
     * @param mixed $couleur
     */
    public function setCouleur($couleur)
    {
        if(!empty($couleur)) {
            $this->couleur = $couleur;
            if (empty($this->colorLitte)) {
                $this->colorLitte = $couleur;
            }
            if (empty($this->colorGfx)) {
                $this->colorGfx = $couleur;
            }
            if (empty($this->colorRp)) {
                $this->colorRp = $couleur;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCouleur()
    {
        return $this->couleur;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->getCouleur();
    }

    /**
     * @return mixed
     */
    public function getColorGfx()
    {
        if(!empty($this->colorGfx)) {
            return $this->colorGfx;
        } else {
            return $this->getColor();
        }
    }

    /**
     * @param mixed $colorGfx
     */
    public function setColorGfx($colorGfx)
    {
        if(!empty($colorGfx))
        $this->colorGfx = $colorGfx;
    }

    /**
     * @return mixed
     */
    public function getColorLitte()
    {
        if(!empty($this->colorLitte)) {
            return $this->colorLitte;
        } else {
            return $this->getColor();
        }
    }

    /**
     * @param mixed $colorLitte
     */
    public function setColorLitte($colorLitte)
    {
        if(!empty($colorLitte))
            $this->colorLitte = $colorLitte;
    }

    /**
     * @return mixed
     */
    public function getColorRp()
    {
        if(!empty($this->colorRp)) {
            return $this->colorRp;
        } else {
            return $this->getColor();
        }
    }

    /**
     * @param mixed $colorRp
     */
    public function setColorRp($colorRp)
    {
        if(!empty($colorRp))
        $this->colorRp = $colorRp;
    }

    /**
     * @param mixed $invisible
     */
    public function setInvisible($invisible)
    {
        $this->invisible = $invisible;
    }

    /**
     * @return mixed
     */
    public function getInvisible()
    {
        return $this->invisible;
    }


    /**
     * Constructor
     */
    public function __construct($name, $roles)
    {
        parent::__construct($name, $roles);
        $this->userRoles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->permission = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ordre
     *
     * @param integer $ordre
     * @return Group
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
     * Add users
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $users
     * @return Group
     */
    public function addUser(\TerAelis\UserBundle\Entity\UserRole $users)
    {
        $this->userRoles[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $users
     */
    public function removeUser(\TerAelis\UserBundle\Entity\UserRole $users)
    {
        $this->userRoles->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * Add permission
     *
     * @param \TerAelis\ForumBundle\Entity\Permission $permission
     * @return Group
     */
    public function addPermission(\TerAelis\ForumBundle\Entity\Permission $permission)
    {
        $this->permission[] = $permission;

        return $this;
    }

    /**
     * Remove permission
     *
     * @param \TerAelis\ForumBundle\Entity\Permission $permission
     */
    public function removePermission(\TerAelis\ForumBundle\Entity\Permission $permission)
    {
        $this->permission->removeElement($permission);
    }

    /**
     * Get permission
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Group
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
     * Add userRoles
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $userRoles
     * @return Group
     */
    public function addUserRole(\TerAelis\UserBundle\Entity\UserRole $userRoles)
    {
        $this->userRoles[] = $userRoles;

        return $this;
    }

    /**
     * Remove userRoles
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $userRoles
     */
    public function removeUserRole(\TerAelis\UserBundle\Entity\UserRole $userRoles)
    {
        $this->userRoles->removeElement($userRoles);
    }
}
