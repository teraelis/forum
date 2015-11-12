<?php

namespace TerAelis\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Post
 *
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="TerAelis\ForumBundle\Entity\PageRepository")
 */
class Page {
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
     * @ORM\Column(name="pageTitle", type="string", length=255)
     */
    private $pageTitle;

    /**
     * @Gedmo\Slug(fields={"pageTitle"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $pagePermalink;

    /**
     * @var string
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle() {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     *
     * @return $this
     */
    public function setPageTitle($pageTitle) {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPagePermalink() {
        return $this->pagePermalink;
    }

    /**
     * @param mixed $pagePermalink
     *
     * @return $this
     */
    public function setPagePermalink($pagePermalink) {
        $this->pagePermalink = $pagePermalink;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

}