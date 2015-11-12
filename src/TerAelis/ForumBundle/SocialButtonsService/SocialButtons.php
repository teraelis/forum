<?php

namespace TerAelis\ForumBundle\SocialButtonsService;

use Symfony\Component\Templating\EngineInterface;

class SocialButtons {

    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating  = $templating;
    }

    public function socialButtons($parameters)
    {
        return $this->templating->render('TerAelisForumBundle:SocialButtons:socialButtons.html.twig', $parameters);
    }

    public function facebookButton($parameters)
    {
        return $this->templating->render('TerAelisForumBundle:SocialButtons:facebook.html.twig', $parameters);
    }

    public function googleplusButton($parameters)
    {
        return $this->templating->render('TerAelisForumBundle:SocialButtons:google.html.twig', $parameters);
    }
}