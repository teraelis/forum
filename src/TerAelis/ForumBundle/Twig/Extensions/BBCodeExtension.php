<?php

namespace TerAelis\ForumBundle\Twig\Extensions;

use Symfony\Component\DependencyInjection\ContainerInterface;

class BBCodeExtension extends \Twig_Extension{
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
        return 'ter_aelis_bbcode_extension';
    }

    public function getFunctions()
    {
        return array(
            'bbcodeInput' => new \Twig_Function_Method($this, 'bbcodeInput', array('is_safe' => array('html'))),
        );
    }

    public function bbcodeInput($id, $errors, $widget)
    {
        return $this->container->get('ter_aelis_forum.BBCode')->BBCode($id, $errors, $widget);
    }
}