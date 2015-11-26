<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use TerAelis\CommentBundle\Entity\Thread;
use TerAelis\UserBundle\Entity\User;

/**
 * Post
 *
 * @ORM\Table(name="ta_post")
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\PostRepository")
 */
class Post
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_publication", type="datetime")
     */
    private $datePublication;

    /**
     * var \DateTime
     *
     * @ORM\Column(name="date_edition", type="datetime", nullable=true)
     */
    private $date_edition;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Regex(pattern="#\w+#", message="Le titre ne peut pas être composé que de caractères spéciaux.")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_title", type="string", length=500, nullable=true)
     */
    private $subTitle;


    /**
    * @Gedmo\Slug(fields={"title"}, updatable=false)
    * @ORM\Column(length=128, unique=true)
    */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\TypeSujet")
     * @ORM\JoinColumn(nullable=true, onDelete="cascade")
     */
    private $typeSujet;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="publie", type="boolean")
     */
    private $publie;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Categorie", inversedBy="posts")
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $mainCategorie;

    /**
     * @ORM\ManyToMany(targetEntity="TerAelis\ForumBundle\Entity\Categorie")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="TerAelis\ForumBundle\Entity\Tag", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $tags = null;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\ForumBundle\Entity\FormulaireDonnees", mappedBy="post", cascade={"persist"})
     */
    private $formulaireDonnees;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Balise", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $balise;

    /**
     * @ORM\OneToOne(targetEntity="TerAelis\ForumBundle\Entity\Sondage", cascade={"remove"}, inversedBy="post")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sondage;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\CommentBundle\Entity\Thread", cascade={"persist", "remove"}, mappedBy="post")
     * @ORM\JoinColumn(nullable=true)
     */
    private $threads;

    /**
     * @ORM\ManyToMany(targetEntity="TerAelis\UserBundle\Entity\User", inversedBy="posts")
     */
    private $authors;

    /**
     * @ORM\Column(name="last_comment", type="datetime", nullable=true)
     */
    private $lastComment;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $lastAuthor;

    /**
     * @ORM\Column(name="number_comment", type="integer")
     */
    private $numberComment = 0;

    /**
     * @ORM\OneToMany(targetEntity="TerAelis\StatistiquesBundle\Entity\View", cascade={"persist", "remove"}, mappedBy="post")
     * @ORM\JoinColumn(nullable=true)
     */
    private $views;

    private $new = false;

    /**
     * Constructor
     */
    public function __contruct() {
        $this->datePublication = new \DateTime('now');
        $this->date_edition = $this->datePublication;
        $this->publie = true;
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->estPremierPost = true;
        $this->estVerrouille = false;
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

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Set date_publication
     *
     * @param \DateTime $datePublication
     * @return Post
     */
    public function setDatePublication($datePublication)
    {
        $this->datePublication = $datePublication;

        return $this;
    }

    /**
     * Get date_publication
     *
     * @return \DateTime 
     */
    public function getDatePublication()
    {
        return $this->datePublication;
    }

    /**
     * Set date_edition
     *
     * @param \DateTime $date_edition
     * @return Post
     */
    public function setDateEdition($date_edition)
    {
        $this->date_edition = $date_edition;

        return $this;
    }

    /**
     * Get date_edition
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->date_edition;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
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
     * Set publie
     *
     * @param boolean $publie
     * @return Post
     */
    public function setPublie($publie)
    {
        $this->publie = $publie;

        return $this;
    }

    /**
     * Get publie
     *
     * @return boolean
     */
    public function getPublie()
    {
        return $this->publie;
    }

    /**
     * Add categories
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $categories
     * @return Post
     */
    public function addCategory(\TerAelis\ForumBundle\Entity\Categorie $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $categories
     */
    public function removeCategory(\TerAelis\ForumBundle\Entity\Categorie $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set estPremierPost
     *
     * @param boolean $estPremierPost
     * @return Post
     */
    public function setEstPremierPost($estPremierPost)
    {
        $this->estPremierPost = $estPremierPost;

        return $this;
    }

    /**
     * Get estPremierPost
     *
     * @return boolean 
     */
    public function getEstPremierPost()
    {
        return $this->estPremierPost;
    }

    /**
     * Set reponses
     *
     * @param \TerAelis\ForumBundle\Entity\Post $reponses
     * @return Post
     */
    public function setReponses(\TerAelis\ForumBundle\Entity\Post $reponses = null)
    {
        $this->reponses = $reponses;

        return $this;
    }

    /**
     * Get reponses
     *
     * @return \TerAelis\ForumBundle\Entity\Post 
     */
    public function getReponses()
    {
        return $this->reponses;
    }

    /**
     * Set estVerrouille
     *
     * @param boolean $estVerrouille
     * @return Post
     */
    public function setEstVerrouille($estVerrouille)
    {
        $this->estVerrouille = $estVerrouille;

        return $this;
    }

    /**
     * Get estVerrouille
     *
     * @return boolean 
     */
    public function getEstVerrouille()
    {
        return $this->estVerrouille;
    }

    /**
     * Set pere
     *
     * @param \TerAelis\ForumBundle\Entity\Post $pere
     * @return Post
     */
    public function setPere(\TerAelis\ForumBundle\Entity\Post $pere = null)
    {
        $this->pere = $pere;

        return $this;
    }

    /**
     * Get pere
     *
     * @return \TerAelis\ForumBundle\Entity\Post 
     */
    public function getPere()
    {
        return $this->pere;
    }

    /**
     * Add tags
     *
     * @param \TerAelis\ForumBundle\Entity\Tag $tags
     * @return Post
     */
    public function addTag(\TerAelis\ForumBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \TerAelis\ForumBundle\Entity\Tag $tags
     */
    public function removeTag(\TerAelis\ForumBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
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
     * Add formulaireDonnees
     *
     * @param \TerAelis\ForumBundle\Entity\FormulaireDonnees $formulaireDonnees
     * @return Post
     */
    public function addFormulaireDonnee(\TerAelis\ForumBundle\Entity\FormulaireDonnees $formulaireDonnees)
    {
        $this->formulaireDonnees[] = $formulaireDonnees;

        return $this;
    }

    /**
     * Remove formulaireDonnees
     *
     * @param \TerAelis\ForumBundle\Entity\FormulaireDonnees $formulaireDonnees
     */
    public function removeFormulaireDonnee(\TerAelis\ForumBundle\Entity\FormulaireDonnees $formulaireDonnees)
    {
        $this->formulaireDonnees->removeElement($formulaireDonnees);
    }

    /**
     * Get formulaireDonnees
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFormulaireDonnees()
    {
        return $this->formulaireDonnees;
    }

    /**
     * Set balise
     *
     * @param \TerAelis\ForumBundle\Entity\Balise $balise
     * @return Post
     */
    public function setBalise(\TerAelis\ForumBundle\Entity\Balise $balise = null)
    {
        $this->balise = $balise;

        return $this;
    }

    /**
     * Get balise
     *
     * @return \TerAelis\ForumBundle\Entity\Balise 
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Add mainCategorie
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $mainCategorie
     * @return Post
     */
    public function addMainCategorie(\TerAelis\ForumBundle\Entity\Categorie $mainCategorie)
    {
        $this->mainCategorie[] = $mainCategorie;

        return $this;
    }

    /**
     * Remove mainCategorie
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $mainCategorie
     */
    public function removeMainCategorie(\TerAelis\ForumBundle\Entity\Categorie $mainCategorie)
    {
        $this->mainCategorie->removeElement($mainCategorie);
    }

    /**
     * Get mainCategorie
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMainCategorie()
    {
        return $this->mainCategorie;
    }

    /**
     * Set mainCategorie
     *
     * @param \TerAelis\ForumBundle\Entity\Categorie $mainCategorie
     * @return Post
     */
    public function setMainCategorie(\TerAelis\ForumBundle\Entity\Categorie $mainCategorie = null)
    {
        $this->mainCategorie = $mainCategorie;

        return $this;
    }

    /**
     * Set typeSujet
     *
     * @param \TerAelis\ForumBundle\Entity\TypeSujet $typeSujet
     * @return Post
     */
    public function setTypeSujet(\TerAelis\ForumBundle\Entity\TypeSujet $typeSujet = null)
    {
        $this->typeSujet = $typeSujet;

        return $this;
    }

    /**
     * Get typeSujet
     *
     * @return \TerAelis\ForumBundle\Entity\TypeSujet 
     */
    public function getTypeSujet()
    {
        return $this->typeSujet;
    }


    /**
     * @param \TerAelis\ForumBundle\Entity\Sondage $sondage
     */
    public function setSondage($sondage)
    {
        $this->sondage = $sondage;
    }


    /**
     * @return \TerAelis\ForumBundle\Entity\Sondage
     */
    public function getSondage()
    {
        return $this->sondage;
    }

    /**
     * Add threads
     *
     * @param \TerAelis\CommentBundle\Entity\Thread $threads
     * @return Post
     */
    public function addThread(\TerAelis\CommentBundle\Entity\Thread $threads)
    {
        $this->threads[] = $threads;

        return $this;
    }

    /**
     * Remove threads
     *
     * @param \TerAelis\CommentBundle\Entity\Thread $threads
     */
    public function removeThread(\TerAelis\CommentBundle\Entity\Thread $threads)
    {
        $this->threads->removeElement($threads);
    }

    /**
     * Get threads
     *
     * @return Thread[]
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * Add authors
     *
     * @param \TerAelis\UserBundle\Entity\User $authors
     * @return Post
     */
    public function addAuthor(\TerAelis\UserBundle\Entity\User $author)
    {
        $this->authors[] = $author;
        if($this->getLastAuthor() == null) {
            $this->setLastAuthor($author);
        }
        return $this;
    }

    /**
     * Remove authors
     *
     * @param \TerAelis\UserBundle\Entity\User $authors
     */
    public function removeAuthor(\TerAelis\UserBundle\Entity\User $authors)
    {
        $this->authors->removeElement($authors);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * isAuthor
     * @return boolean
     */
    public function isAuthor(User $user) {
        return $this->authors->contains($user);
    }

    /**
     * Set lastComment
     *
     * @param \DateTime $lastComment
     * @return Post
     */
    public function setLastComment($lastComment = null, $force = false)
    {
        if(!$force && $lastComment == null) {
            $this->lastComment = $this->datePublication;
        } else {
            $this->lastComment = $lastComment;
        }
        return $this;
    }

    /**
     * Get lastComment
     *
     * @return \DateTime 
     */
    public function getLastComment()
    {
        return $this->lastComment;
    }

    /**
     * Set numberComment
     *
     * @param integer $numberComment
     * @return Post
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

    /**
     * @param mixed $lastAuthor
     */
    public function setLastAuthor($lastAuthor) {
        $this->lastAuthor = $lastAuthor;
    }

    /**
     * @return mixed
     */
    public function getLastAuthor() {
        if($this->lastAuthor == null && $this->authors != null && method_exists($this->authors, 'first'))
            $this->lastAuthor = $this->authors->first();
        return $this->lastAuthor;
    }

    /**
     * @param boolean $new
     */
    public function setNew() {
        $this->forceNew(true);
    }

    public function forceNew($new) {
        $this->new = $new;
    }

    /**
     * @return boolean
     */
    public function getNew() {
        return $this->new;
    }

    /**
     * @return string
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * @param string $subTitle
     * @return Post
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        if(!empty($this->createdAt)) {
            return $this->createdAt;
        } else {
            return $this->datePublication;
        }
    }

    /**
     * @param \DateTime $createdAt
     * @return Post
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setTagsNull()
    {
        $this->tags = [];
    }
}
