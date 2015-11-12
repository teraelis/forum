<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Categorie
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="ta_categorie")
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\CategorieRepository")
 */
class Categorie
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string", length=128)
     */
    private $title;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Categorie", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\Categorie", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
    * @var string
    *
    * @Gedmo\Slug(fields={"title"})
    * @ORM\Column(length=128, unique=true)
    */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="writable", type="boolean")
     */
    private $writable;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\FormulaireType", mappedBy="categorie", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\Count(min = "1", minMessage = "Vous devez avoir au moins un formulaire")
     */
    private $formulaire;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\Balise", mappedBy="categorie", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $balise;

    /**
     * @var boolean
     *
     * @ORM\Column(name="baliseObligatoire", type="boolean", nullable=false)
     */
    private $baliseObligatoire;

    /**
     * @var integer
     * @ORM\Column(name="number_post", type="integer")
     */
    private $numberPost = 0;

    /**
     * @var integer
     * @ORM\Column(name="number_comment", type="integer")
     */
    private $numberComment = 0;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $lastPost = null;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\Post", mappedBy="mainCategorie")
     */
    private $posts = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image = null;

    private $file = null;

    private $new = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Categorie
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Categorie
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
     * Set lft
     *
     * @param integer $lft
     * @return Categorie
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer 
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return Categorie
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer 
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return Categorie
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer 
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return Categorie
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set parent
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $parent
     * @return Categorie
     */
    public function setParent(\TerAelis\ForumBundle\Entity\Categorie $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \TerAelis\ForumBundle\Entity\Categorie 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $children
     * @return Categorie
     */
    public function addChild(\TerAelis\ForumBundle\Entity\Categorie $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $children
     */
    public function removeChild(\TerAelis\ForumBundle\Entity\Categorie $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Categorie
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Set writable
     *
     * @param boolean $writable
     * @return Categorie
     */
    public function setWritable($writable)
    {
        $this->writable = $writable;

        return $this;
    }

    /**
     * Get writable
     *
     * @return boolean 
     */
    public function getWritable()
    {
        return $this->writable;
    }

    /**
     * Set formulaire
     *
     * @param ArrayCollection $formulaires
     * @return Categorie
     */
    public function setFormulaire(ArrayCollection $formulaires)
    {
        foreach ($formulaires as $f) {
            $f->setCategorie($this);
        }

        $this->formulaire = $formulaires;
        return $this;
    }

    /**
     * Add formulaire
     *
     * @param \TerAelis\ForumBundle\Entity\FormulairePost $formulaire
     * @return Categorie
     */
    public function addFormulaire(\TerAelis\ForumBundle\Entity\FormulaireType $formulaire)
    {
        $this->formulaire[] = $formulaire;

        return $this;
    }

    /**
     * Remove formulaire
     *
     * @param \TerAelis\ForumBundle\Entity\FormulairePost $formulaire
     */
    public function removeFormulaire(\TerAelis\ForumBundle\Entity\FormulaireType $formulaire)
    {
        $this->formulaire->removeElement($formulaire);
    }

    /**
     * Get formulaire
     *
     * @return ArrayCollection
     */
    public function getFormulaire()
    {
        return $this->formulaire;
    }

    /**
     * Add balise
     *
     * @param \TerAelis\ForumBundle\Entity\Balise $balise
     * @return Categorie
     */
    public function addBalise(\TerAelis\ForumBundle\Entity\Balise $balise)
    {
        $this->balise[] = $balise;

        return $this;
    }

    /**
     * Remove balise
     *
     * @param \TerAelis\ForumBundle\Entity\FormulaireType $balise
     */
    public function removeBalise(\TerAelis\ForumBundle\Entity\FormulaireType $balise)
    {
        $this->balise->removeElement($balise);
    }

    /**
     * Get balise
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Has Balise
     *
     * @return boolean
     */
    public function hasBalise() {
        return !$this->balise->isEmpty();
    }

    /**
     * Set baliseObligatoire
     *
     * @param boolean $baliseObligatoire
     * @return Categorie
     */
    public function setBaliseObligatoire($baliseObligatoire)
    {
        $this->baliseObligatoire = $baliseObligatoire;

        return $this;
    }

    /**
     * Get baliseObligatoire
     *
     * @return boolean 
     */
    public function getBaliseObligatoire()
    {
        return $this->baliseObligatoire;
    }

    /**
     * Set numberPost
     *
     * @param integer $numberPost
     * @return Categorie
     */
    public function setNumberPost($numberPost)
    {
        $this->numberPost = $numberPost;

        return $this;
    }

    /**
     * Get numberPost
     *
     * @return integer 
     */
    public function getNumberPost()
    {
        return $this->numberPost;
    }

    /**
     * Set numberComment
     *
     * @param integer $numberComment
     * @return Categorie
     */
    public function setNumberComment($numberComment)
    {
        $this->numberComment = $numberComment;

        return $this;
    }

    /**
     * Get numberComment
     *
     * @return integer 
     */
    public function getNumberComment()
    {
        return $this->numberComment;
    }

    public function forceLastPost(Post $lastPost) {
        $this->lastPost = $lastPost;
    }

    /**
     * @param Post|null $lastPost
     */
    public function setLastPost($lastPost, $force = false) {
        if($force || $lastPost === null || $this->lastPost === null || $this->lastPost->getLastComment() < $lastPost->getLastComment())
            $this->lastPost = $lastPost;
    }

    /**
     * @return mixed
     */
    public function getLastPost() {
        return $this->lastPost;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     * @return Categorie
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param null $file
     * @return Categorie
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function getUploadDir()
    {
        return 'uploads/img/categorie';
    }

    protected function getUploadRootDir()
    {
        // On retourne le chemin relatif vers l'image pour notre code PHP
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
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
        $name = rand(1, 999999).'.'.$extension;

        $this->file->move($this->getUploadRootDir(), $name);
        $this->image = $this->getUploadDir().'/'.$name;
    }

    /**
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * @param boolean $new
     */
    public function setNew($new)
    {
        $this->new = $new;
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
     * @return Categorie
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
        return $this;
    }
}
