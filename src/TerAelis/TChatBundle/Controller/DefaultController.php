<?php

namespace TerAelis\TChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TerAelisTChatBundle:Default:index.html.twig', array('name' => $name));
    }
}
