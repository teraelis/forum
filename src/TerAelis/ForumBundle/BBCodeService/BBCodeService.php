<?php

namespace TerAelis\ForumBundle\BBCodeService;

use Symfony\Component\Templating\EngineInterface;

class BBCodeService {
    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating  = $templating;
    }

    public function BBCode($id, $errors, $widget)
    {
        return $this->templating->render('TerAelisForumBundle:BBCode:main.html.twig', array('id' => $id, 'errors' => $errors, 'widget' => $widget));
    }
}