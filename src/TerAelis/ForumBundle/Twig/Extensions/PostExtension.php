<?php

namespace TerAelis\ForumBundle\Twig\Extensions;

use IntlDateFormatter;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use TerAelis\CommentBundle\Entity\Comment;
use TerAelis\ForumBundle\Entity\Post;
use TerAelis\UserBundle\Service\UpdateUser;

class PostExtension extends \Twig_Extension {

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var UpdateUser
     */
    private $updateUser;

    /**
     * Constructor.
     * @param Router $router
     * @param Session $session
     * @param UpdateUser $updateUser
     */
    public function __construct(Router $router, Session $session, UpdateUser $updateUser)
    {
        mb_internal_encoding('utf-8');
        $this->router = $router;
        $this->session = $session;
        $this->updateUser = $updateUser;
    }

    public function getName()
    {
        return 'ter_aelis_post_extension';
    }

    public function getFunctions()
    {
        return array(
            'canonical' => new \Twig_Function_Method($this, 'getCanonical', array('is_safe' => array('html'))),
            'authors' => new \Twig_Function_Method($this, 'getAuthors', array('is_safe' => array('html'))),
            'showUsername' => new \Twig_Function_Method($this, 'showUsername', array('is_safe' => array('html'))),
            'tchatWith' => new \Twig_Function_Method($this, 'tchatWith', array('is_safe' => array('html'))),
            'showGroup' => new \Twig_Function_Method($this, 'showGroup', array('is_safe' => array('html'))),
            'showChosenGroup' => new \Twig_Function_Method($this, 'showChosenGroup', array('is_safe' => array('html'))),
            'short' => new \Twig_Function_Method($this, 'short', array('is_safe' => array('html'))),
            'veryShort' => new \Twig_Function_Method($this, 'veryShort', array('is_safe' => array('html'))),
            'shortText' => new \Twig_Function_Method($this, 'shortText', array('is_safe' => array('html'))),
            'shortComment' => new \Twig_Function_Method($this, 'shortComment', array('is_safe' => array('html'))),
            'showDate' => new \Twig_Function_Method($this, 'showDate', array('is_safe' => array('html'))),
            'cut_signature' => new \Twig_Function_Method($this, 'cutSignature', array('is_safe' => array('html'))),
            'setSession' => new \Twig_Function_Method($this, 'setSession', array('is_safe' => array('html'))),
        );
    }

    public function getColor($entity, $defaultColor = false) {
        $pole = $this->session->get('pole_aff');
        if($defaultColor || empty($pole)) {
            $color = $entity->getColor();
        } else {
            switch($pole) {
                case 'litterature':
                    $color = $entity->getColorLitte();
                    break;
                case 'graphisme':
                    $color = $entity->getColorGfx();
                    break;
                case 'rolisme':
                    $color = $entity->getColorRp();
                    break;
                default:
                    $color = $entity->getColor();
            }
        }
        return $color;
    }

    public function getAuthors($authors, $defaultColor = false)
    {
        $str = "Anonyme";
        if(is_object($authors) && method_exists($authors, 'count')) {
            if($authors->count() > 0) {
                $str = "";
                foreach($authors as $author) {
                    $str = $str.", ".$this->showUsername($author, $defaultColor);
                }
                $str = mb_substr($str, 2);
            }
        } else if(is_object($authors)) {
            $str = $this->showUsername($authors, $defaultColor);
        }
        return $str;
    }

    public function tchatWith($user, $defaultColor = false) {
        if(!empty($user)) {
            return "<a class='pseudo' href='" . $this->router->generate('teraelis_tchat_lone_user', array('userId' => $user->getId())) . "' style='color: " . $this->getColor($user, $defaultColor) . "'>" . $user->getUsername() . "</a>";
        } else {
            return 'Anonyme';
        }
    }

    public function showUsername($user, $defaultColor = false) {
        if(!empty($user)) {
            $this->updateUser->updateUser($user);
            return "<a class='pseudo' href='".$this->router->generate('user_profile', array('id' => $user->getId()))."' style='color: ".$this->getColor($user, $defaultColor)."'>".$user->getUsername()."</a>";
        } else {
            return 'Anonyme';
        }
    }

    public function showGroup($group, $defaultColor = false) {
        if(!empty($group)) {
            return "<a class='group' href='".$this->router->generate('group_view', array('id' => $group->getId()))."' style='color: ".$this->getColor($group, $defaultColor)."'>".$group->getName()."</a>";
        } else {
            return '';
        }
    }

    public function showChosenGroup($group, $defaultColor = false) {
        if(!empty($group)) {
            return "<a class='group' href='" . $this->router->generate('group_view', array('id' => $group->getId())) . "' style='color: " . $this->getColor($group, $defaultColor) . "'>" . $group->getName() . "</a>";
        } else {
            return "";
        }
    }

    public function short($title) {
        return strlen($title) > 35 ? mb_substr($title,0,35)."...":$title;
    }

    public function veryShort($title) {
        return strlen($title) > 20 ? mb_substr($title,0,20)."...":$title;
    }

    public function shortText(Post $post) {
        $content = $post->getFormulaireDonnees();
        $res = "";
        foreach($content as $f) {
            $res = $res.$f->getContenu();
            if(strlen($res) >= 150) {
                break;
            }
        }
        return strlen($res) > 150 ? mb_substr($res,0,150)."...":$res;
    }

    public function shortComment(Comment $post) {
        $res = $post->getBody();
        return strlen($res) > 150 ? mb_substr($res,0,150)."...":$res;
    }

    public function showDate(\DateTime $date) {
        $f1 = new IntlDateFormatter('fr_FR', IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
        $f2 = new IntlDateFormatter('fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
        return $f1->format($date) ." - ".$f2->format($date);
    }

    public function getCanonical($currentPath, $pole, $pole_aff) {
        if($pole != $pole_aff) {
            $currentPath = preg_replace('#/(graphisme|rolisme)/#', '/litterature/', $currentPath);
        }
        return $currentPath;
    }

    public function cutSignature($signature) {
        return mb_substr($signature, 0, 200);
    }

    public function setSession($name, $value) {
        $this->session->set($name, $value);
    }
}