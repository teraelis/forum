<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormulaireDonnees
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\FormulaireDonneesRepository")
 */
class FormulaireDonnees
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
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\Post", inversedBy="formulaireDonnees", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="TerAelis\ForumBundle\Entity\FormulaireType")
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="contenu", type="text", nullable=true)
     */
    private $contenu;

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
     * Set contenu
     *
     * @param string $contenu
     * @return FormulaireDonnees
     */
    public function setContenu($contenu)
    {
        if ($contenu == null || $contenu == "") {
            $this->contenu = $this->type->getDefault();
        } else {
            $this->contenu = $contenu;
        }

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string 
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set post
     *
     * @param \TerAelis\ForumBundle\Entity\Post $post
     * @return FormulaireDonnees
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
     * Set type
     *
     * @param \TerAelis\ForumBundle\Entity\FormulaireType $type
     * @return FormulaireDonnees
     */
    public function setType(\TerAelis\ForumBundle\Entity\FormulaireType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \TerAelis\ForumBundle\Entity\FormulaireType 
     */
    public function getType()
    {
        return $this->type;
    }
}
