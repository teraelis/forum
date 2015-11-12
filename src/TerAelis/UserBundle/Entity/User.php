<?php

namespace TerAelis\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\Form\Exception\NotValidException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="TerAelis\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\UserBundle\Entity\UserRole", mappedBy="user")
     */
    protected $userRole;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\FollowedPost", mappedBy="user")
     */
    protected $follow;

    /**
     * @ORM\ManyToMany(targetEntity="TerAelis\ForumBundle\Entity\Post", mappedBy="authors")
     */
    protected $posts;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\CommentBundle\Entity\Comment", mappedBy="author")
     */
    protected $comments;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastVisit;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastUpdate;

    /**
     * @ORM\ManyToMany(targetEntity="\TerAelis\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="fos_user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="\TerAelis\UserBundle\Entity\Rang")
     * @ORM\JoinTable(name="user_rang",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rang_id", referencedColumnName="id")}
     * )
     */
    protected $rangs;

    /**
     * @ORM\JoinTable(name="salon_user")
     * @ORM\ManyToMany(targetEntity="\TerAelis\TChatBundle\Entity\Salon", mappedBy="users")
     */
    protected $salons;

    /**
     * @ORM\OneToMany(targetEntity="\TerAelis\ForumBundle\Entity\NonVu", mappedBy="user")
     */
    protected $nonVus;

    /**
     * @ORM\OneToMany(targetEntity="\TerAelis\TChatBundle\Entity\NonVuTChat", mappedBy="user")
     */
    protected $nonVusTChat;

    /**
     * @ORM\Column(type="string", length=7)
     */
    protected $color = "#000000";

    /**
     * @ORM\Column(type="string", length=7)
     */
    protected $colorLitte = "#000000";

    /**
     * @ORM\Column(type="string", length=7)
     */
    protected $colorGfx = "#ffffff";

    /**
     * @ORM\Column(type="string", length=7)
     */
    protected $colorRp = "#ffffff";

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\Group")
     * @ORM\JoinColumn(name="chosen_group_id", referencedColumnName="id")
     */
    protected $chosenGroup = null;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $skype;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $facebook;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $twitter;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $deviantArt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $site;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $allowMail = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $showMail = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible = true;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbMessages = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbSujets = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbCommentaires = 0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $avatar = null;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    /**
     * @ORM\Column(type="text", length=1000, nullable=true)
     */
    protected $signature;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $biographie;

    /**
     * @ORM\Column(type="string")
     */
    protected $pole = 'litterature';


    public function __construct()
    {
        parent::__construct();
        $this->roles[] = "ROLE_USER";
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param mixed $lastVisit
     */
    public function setLastVisit($lastVisit = null)
    {
        if($lastVisit == null) {
            $this->lastVisit = new \DateTime();
        } else {
            $this->lastVisit = $lastVisit;
        }
    }

    /**
     * @return mixed
     */
    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    /**
     * @param mixed $groupes
     */
    public function setUserRole($groupes)
    {
        $this->userRole = $groupes;
    }

    /**
     * @return mixed
     */
    public function getUserRole()
    {
        return $this->userRole;
    }



    /**
     * Add groups
     *
     * @param \FOS\UserBundle\Model\GroupInterface $groups
     */
    public function addGroup(\FOS\UserBundle\Model\GroupInterface $groups)
    {
        $this->groups[] = $groups;
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }


    /**
     * Remove Group
     *
     * @param \FOS\UserBundle\Model\GroupInterface $group
     */
    public function removeGroup(GroupInterface $group)
    {
        $this->groups->removeElement($group);
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
     * Add userRole
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $userRole
     * @return User
     */
    public function addUserRole(\TerAelis\UserBundle\Entity\UserRole $userRole)
    {
        $this->userRole[] = $userRole;

        return $this;
    }

    /**
     * Remove userRole
     *
     * @param \TerAelis\UserBundle\Entity\UserRole $userRole
     */
    public function removeUserRole(\TerAelis\UserBundle\Entity\UserRole $userRole)
    {
        $this->userRole->removeElement($userRole);
    }

    /**
     * Add follow
     *
     * @param \TerAelis\ForumBundle\Entity\FollowedPost $follow
     * @return User
     */
    public function addFollow(\TerAelis\ForumBundle\Entity\FollowedPost $follow)
    {
        $this->follow[] = $follow;

        return $this;
    }

    /**
     * Remove follow
     *
     * @param \TerAelis\ForumBundle\Entity\FollowedPost $follow
     */
    public function removeFollow(\TerAelis\ForumBundle\Entity\FollowedPost $follow)
    {
        $this->follow->removeElement($follow);
    }

    /**
     * Get follow
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFollow()
    {
        return $this->follow;
    }

    /**
     * Add nonVus
     *
     * @param \TerAelis\ForumBundle\Entity\NonVu $nonVus
     * @return User
     */
    public function addNonVus(\TerAelis\ForumBundle\Entity\NonVu $nonVus)
    {
        $this->nonVus[] = $nonVus;

        return $this;
    }

    /**
     * Remove nonVus
     *
     * @param \TerAelis\ForumBundle\Entity\NonVu $nonVus
     */
    public function removeNonVus(\TerAelis\ForumBundle\Entity\NonVu $nonVus)
    {
        $this->nonVus->removeElement($nonVus);
    }

    /**
     * Get nonVus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNonVus()
    {
        return $this->nonVus;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return User
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
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
        $this->colorRp = $colorRp;
    }

    /**
     * @return mixed
     */
    public function getChosenGroup()
    {
        return $this->chosenGroup;
    }

    /**
     * @param Group $chosenGroup
     */
    public function setChosenGroup($chosenGroup)
    {
        $this->setColor($chosenGroup->getColor());
        $this->setColorLitte($chosenGroup->getColorLitte());
        $this->setColorGfx($chosenGroup->getColorGfx());
        $this->setColorRp($chosenGroup->getColorRp());
        $this->chosenGroup = $chosenGroup;
    }

    /**
     * Set skype
     *
     * @param string $skype
     * @return User
     */
    public function setSkype($skype)
    {
        $this->skype = $skype;

        return $this;
    }

    /**
     * Get skype
     *
     * @return string 
     */
    public function getSkype()
    {
        return $this->skype;
    }

    /**
     * Set allowMail
     *
     * @param boolean $allowMail
     * @return User
     */
    public function setAllowMail($allowMail)
    {
        $this->allowMail = $allowMail;

        return $this;
    }

    /**
     * Get allowMail
     *
     * @return boolean 
     */
    public function getAllowMail()
    {
        return $this->allowMail;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     * @return User
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     * @return User
     */
    public function setLastUpdate($lastUpdate = null)
    {
        if($lastUpdate == null) {
            $this->lastUpdate = new \DateTime();
        } else {
            $this->lastUpdate = $lastUpdate;
        }

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime 
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set nbMessages
     *
     * @param integer $nbMessages
     * @return User
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
     * @return User
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
     * Set nbCommentaires
     *
     * @param integer $nbCommentaires
     * @return User
     */
    public function setNbCommentaires($nbCommentaires)
    {
        $this->nbCommentaires = $nbCommentaires;

        return $this;
    }

    /**
     * Get nbCommentaires
     *
     * @return integer 
     */
    public function getNbCommentaires()
    {
        return $this->nbCommentaires;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     * @return User
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * Get facebook
     *
     * @return string 
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     * @return User
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set deviantArt
     *
     * @param string $deviantArt
     * @return User
     */
    public function setDeviantArt($deviantArt)
    {
        $this->deviantArt = $deviantArt;

        return $this;
    }

    /**
     * Get deviantArt
     *
     * @return string 
     */
    public function getDeviantArt()
    {
        return $this->deviantArt;
    }

    /**
     * Set site
     *
     * @param string $site
     * @return User
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string 
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set showMail
     *
     * @param boolean $showMail
     * @return User
     */
    public function setShowMail($showMail)
    {
        $this->showMail = $showMail;

        return $this;
    }

    /**
     * Get showMail
     *
     * @return boolean 
     */
    public function getShowMail()
    {
        return $this->showMail;
    }

    /**
     * Set biographie
     *
     * @param string $biographie
     * @return User
     */
    public function setBiographie($biographie)
    {
        $this->biographie = $biographie;

        return $this;
    }

    /**
     * Get biographie
     *
     * @return string 
     */
    public function getBiographie()
    {
        return $this->biographie;
    }

    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif)
        if (null === $this->file) {
            return;
        }

        // On garde le nom original du fichier de l'internaute
        $extension = $this->file->guessExtension();
        if(!$extension) {
            $extension = 'bin';
        }
        $name = rand(1, 9999999).'.'.$extension;

        $this->file->move($this->getUploadRootDir(), $name);
        $this->avatar = $this->getUploadDir().'/'.$name;
    }

    public function getUploadDir()
    {
        return 'uploads/img/avatar';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    /**
     * Set signature
     *
     * @param string $signature
     * @return User
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string 
     */
    public function getSignature()
    {
        return $this->signature;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * Set pole
     *
     * @param string $pole
     * @return User
     */
    public function setPole($pole)
    {
        $this->pole = $pole;

        return $this;
    }

    /**
     * Get pole
     *
     * @return string 
     */
    public function getPole()
    {
        return $this->pole;
    }

    /**
     * Add rangs
     *
     * @param \TerAelis\UserBundle\Entity\Rang $rangs
     * @return User
     */
    public function addRang(\TerAelis\UserBundle\Entity\Rang $rangs)
    {
        $this->rangs[] = $rangs;

        return $this;
    }

    /**
     * Remove rangs
     *
     * @param \TerAelis\UserBundle\Entity\Rang $rangs
     */
    public function removeRang(\TerAelis\UserBundle\Entity\Rang $rangs)
    {
        $this->rangs->removeElement($rangs);
    }

    /**
     * Get rangs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRangs()
    {
        return $this->rangs;
    }

    /**
     * Add salons
     *
     * @param \TerAelis\TChatBundle\Entity\Salon $salons
     * @return User
     */
    public function addSalon(\TerAelis\TChatBundle\Entity\Salon $salons)
    {
        $this->salons[] = $salons;

        return $this;
    }

    /**
     * Remove salons
     *
     * @param \TerAelis\TChatBundle\Entity\Salon $salons
     */
    public function removeSalon(\TerAelis\TChatBundle\Entity\Salon $salons)
    {
        $this->salons->removeElement($salons);
    }

    /**
     * Get salons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSalons()
    {
        return $this->salons;
    }

    /**
     * @return mixed
     */
    public function getNonVusTChat()
    {
        return $this->nonVusTChat;
    }

    /**
     * @param mixed $nonVusTChat
     */
    public function setNonVusTChat($nonVusTChat)
    {
        $this->nonVusTChat = $nonVusTChat;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param mixed $posts
     * @return User
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     * @return User
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }
}
