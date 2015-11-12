<?php

namespace TerAelis\ForumBundle\Twig\Extensions;

class SocialButtonsBar extends \Twig_Extension{

    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'ter_aelis_social_bar';
    }

    public function getFunctions()
    {
        return array(
            'socialButtons' => new \Twig_Function_Method($this, 'getSocialButtons' ,array('is_safe' => array('html'))),
            'facebookButton' => new \Twig_Function_Method($this, 'getFacebookButton' ,array('is_safe' => array('html'))),
            'googleplusButton' => new \Twig_Function_Method($this, 'getGooglePlusButton' ,array('is_safe' => array('html'))),
        );
    }

    public function getSocialButtons($parameters = array())
    {
        // get the helper service and display the template
        return $this->container->get('ter_aelis_forum.socialButtons')->socialButtons($parameters);
    }

    // https://developers.facebook.com/docs/reference/plugins/like/
    public function getFacebookButton($parameters = array())
    {
        return $this->container->get('ter_aelis_forum.socialButtons')->facebookButton($parameters);
    }

    public function getGooglePlusButton($parameters = array()) {
        return $this->container->get('ter_aelis_forum.socialButtons')->googleplusButton($parameters);
    }
}