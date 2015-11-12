<?php

namespace TerAelis\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as UniqueEntity;

/**
 * UserRole
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\UserBundle\Entity\UserRoleRepository")
 */
class UserRole
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
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User", cascade={"persist"}, inversedBy="userRole")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\Group", cascade={"persist"}, inversedBy="userRoles")
     */
    private $groupe;

    /**
     * @var string
     *
     * @ORM\Column(name="role", type="string", length=3)
     */
    private $role;


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
     * Set user
     *
     * @param \stdClass $user
     * @return UserRole
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \stdClass 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set groupe
     *
     * @param \stdClass $groupe
     * @return UserRole
     */
    public function setGroupe($groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \stdClass 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return UserRole
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }
}
